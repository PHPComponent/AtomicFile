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
            throw FileLockException::createForFailedToLockFile($this->getFilePath());
        }
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
            throw FileOperationException::createForFailedToWriteToFile($this->getFilePath());
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
            throw FileOperationException::createForFailedToTruncateFile($this->getFilePath());
        }
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
     * @return string
     */
    protected function getOpenMode()
    {
        return 'cb';
    }
}