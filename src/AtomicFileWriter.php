<?php
/*
 * This file is part of PHPComponent/AtomicFile.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\AtomicFile;

use PHPComponent\AtomicFile\Exceptions\FileLockException;
use PHPComponent\AtomicFile\Exceptions\FileOperationException;
use PHPComponent\AtomicFile\Exceptions\WriteOnlyFileException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class AtomicFileWriter extends AtomicFileHandler implements IAtomicFileWriter
{

    const OPEN_WRITE_ONLY = true;
    const OPEN_NOT_WRITE_ONLY = false;

    /** @var bool */
    private $create_non_existent;

    /** @var bool */
    private $write_only;

    /** @var resource */
    private $original_file;

    /** @var string */
    private $temp_file_path;

    /**
     * @param string $file_path
     * @param bool $create_non_existent
     * @param bool $write_only
     * @param int $lock_retries
     * @param int $retry_interval
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $file_path,
        $create_non_existent = false,
        $write_only = self::OPEN_NOT_WRITE_ONLY,
        $lock_retries = self::LOCK_RETRIES,
        $retry_interval = self::RETRY_INTERVAL
    )
    {
        $this->create_non_existent = $create_non_existent;
        $this->write_only = $write_only;
        parent::__construct($file_path, $lock_retries, $retry_interval);
        $this->temp_file_path = $this->getFilePath().'-'.rand().'.tmp';
    }

    /**
     * @return $this
     * @throws FileLockException
     * @throws FileOperationException
     */
    public function openFile()
    {
        if($this->isFileOpened()) return $this;
        if(!$this->isCreateNonExistent())
        {
            $this->checkFile(true);
        }

        $this->setFile(fopen($this->getFilePath(), $this->getOpenMode()));
        if(!is_resource($this->getFile()))
        {
            throw FileOperationException::createForFailedToOpenFile($this->getFilePath());
        }
        if(!$this->tryLockFile(LOCK_EX, $this->getLockRetries(), $this->getRetryInterval()))
        {
            $this->rollbackOpeningFile();
            throw FileLockException::createForFailedToLockFile($this->getFilePath());
        }

        $temp_file = fopen($this->getTempFilePath(), $this->getOpenMode());
        if(!is_resource($temp_file))
        {
            $this->rollbackOpeningFile($temp_file);
            throw FileOperationException::createForFailedToOpenTempFile($this->getTempFilePath());
        }

        $copied_bytes = stream_copy_to_stream($this->getFile(), $temp_file);
        if($copied_bytes !== $this->getFileSize())
        {
            $this->rollbackOpeningFile($temp_file);
            throw FileOperationException::createForFailedToCopyIntoTempFile($this->getTempFilePath());
        }

        $this->original_file = $this->getFile();
        //execute all functions on temp file
        $this->setFile($temp_file);

        return $this;
    }

    /**
     * @param string|int|bool $text
     * @return int
     * @throws FileOperationException
     * @throws \InvalidArgumentException
     */
    public function writeToFile($text)
    {
        if(!is_scalar($text)) throw new \InvalidArgumentException('Text must be scalar instead of '.gettype($text));
        $this->openFile();
        $write = fwrite($this->getFile(), $text);
        if($write === false)
        {
            throw FileOperationException::createForFailedToWriteToFile($this->getTempFilePath());
        }
        return $write;
    }

    /**
     * @param int $truncate_size
     * @return $this
     * @throws FileOperationException
     */
    public function truncateFile($truncate_size = 0)
    {
        $this->openFile();
        $truncate = ftruncate($this->getFile(), $truncate_size);
        if(!$truncate)
        {
            throw FileOperationException::createForFailedToTruncateFile($this->getTempFilePath());
        }
        $this->rewindFile();
        return $this;
    }

    /**
     * Return reader
     * @param int $lock_retries
     * @param int $retry_interval
     * @return IAtomicFileReader
     * @throws WriteOnlyFileException
     */
    public function getReader($lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL)
    {
        if($this->isWriteOnly())
        {
            throw WriteOnlyFileException::createForWriteOnly($this->getFilePath());
        }
        $this->openFile();
        $reader = new AtomicFileReader($this->getFilePath(), $lock_retries, $retry_interval);
        $this->closeFile();
        return $reader->openFile();
    }

    /**
     * @inheritdoc
     */
    public function closeFile()
    {
        if(!$this->isOriginalFileOpened() && !$this->isFileOpened()) return;
        $this->unlockFile();
        fclose($this->original_file);
        fclose($this->getFile());
        if(!rename($this->getTempFilePath(), $this->getFilePath()))
        {
            unlink($this->getTempFilePath());
        }
    }

    /**
     * @inheritdoc
     */
    protected function unlockFile()
    {
        if(!$this->isOriginalFileOpened()) return;
        $unlock = flock($this->original_file, LOCK_UN);
        if(!$unlock)
        {
            throw FileLockException::createForFailedToUnlockFile($this->getFilePath());
        }
    }

    /**
     * Used only when failed to open temp file in OpenFile
     * @param resource|null $temp_file
     * @return void
     */
    private function rollbackOpeningFile($temp_file = null)
    {
        flock($this->getFile(), LOCK_UN);
        fclose($this->getFile());
        if(is_resource($temp_file))
        {
            fclose($temp_file);
            unlink($this->getTempFilePath());
        }
    }

    /**
     * @return bool
     */
    private function isOriginalFileOpened()
    {
        return is_resource($this->original_file);
    }

    /**
     * @return string
     */
    private function getTempFilePath()
    {
        return $this->temp_file_path;
    }

    /**
     * @return bool
     */
    protected function isCreateNonExistent()
    {
        return $this->create_non_existent;
    }

    /**
     * @return bool
     */
    protected function isWriteOnly()
    {
        return $this->write_only;
    }

    /**
     * Open it in reading mode for copying into temp file
     * @return string
     */
    protected function getOpenMode()
    {
        return 'c+b';
    }
}