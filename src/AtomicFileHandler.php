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

use PHPComponent\AtomicFile\Exceptions\AtomicFileException;
use PHPComponent\AtomicFile\Exceptions\FileLockException;
use PHPComponent\AtomicFile\Exceptions\FileOperationException;
use PHPComponent\AtomicFile\Exceptions\NonExistentFileException;
use PHPComponent\AtomicFile\Exceptions\NotReadableFileException;
use PHPComponent\AtomicFile\Exceptions\NotWritableFileException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
abstract class AtomicFileHandler implements IAtomicFileHandler
{

    /** @var string */
    private $file_path;

    /** @var resource */
    private $file;

    /** @var int */
    private $lock_retries;

    /** @var int */
    private $retry_interval;

    /**
     * AtomicFileHandler constructor.
     * @param string $file_path
     * @param int $lock_retries
     * @param int $retry_interval
     * @throws \InvalidArgumentException
     */
    public function __construct($file_path, $lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL)
    {
        if(!is_string($file_path))
        {
            throw new \InvalidArgumentException('File path must be string instead of '.gettype($file_path));
        }
        if(($file_path = trim($file_path)) === '')
        {
            throw new \InvalidArgumentException('File path must not be empty');
        }
        $this->file_path = $file_path;
        $this->lock_retries = $lock_retries;
        $this->retry_interval = $retry_interval;
    }

    /**
     * Rewind the file
     * @return $this
     * @throws AtomicFileException
     */
    public function rewindFile()
    {
        $this->openFile();
        if(!rewind($this->getFile()))
        {
            throw FileOperationException::createForFailedToRewindFile($this->getFilePath());
        }
        return $this;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return $this
     * @throws AtomicFileException
     */
    public function seekFile($offset, $whence = SEEK_SET)
    {
        $this->openFile();
        $seek = fseek($this->getFile(), $offset, $whence);
        if($seek === -1)
        {
            throw FileOperationException::createForFailedToSeekFile($this->getFilePath());
        }
        return $this;
    }

    /**
     * @return $this
     * @throws AtomicFileException
     */
    public function seekFileToEnd()
    {
        $this->seekFile(0, SEEK_END);
        return $this;
    }

    /**
     * @return bool
     * @throws AtomicFileException
     */
    public function isEndOfFile()
    {
        $this->openFile();
        return fgetc($this->getFile()) === false;
    }

    /**
     * Return actual pointer of file resource
     * @return int
     * @throws AtomicFileException
     */
    public function getFilePointer()
    {
        $this->openFile();
        return ftell($this->getFile());
    }

    /**
     * @throws AtomicFileException
     */
    public function closeFile()
    {
        if(!$this->isFileOpened()) return;
        $this->unlockFile();
        $close = fclose($this->file);
        if(!$close)
        {
            throw FileOperationException::createForFailedToCloseFile($this->getFilePath());
        }
    }

    /**
     * Find whether file is empty
     * @return bool
     * @throws AtomicFileException
     */
    public function isFileEmpty()
    {
        $this->openFile();
        $saved_pointer = $this->getFilePointer();
        $this->rewindFile()->seekFileToEnd();
        $size_pointer = $this->getFilePointer();
        $this->seekFile($saved_pointer);

        return $size_pointer === 0;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        $this->openFile();
        $stats = fstat($this->file);
        return $stats['size'];
    }

    /**
     * Find whether file is opened
     * @return bool
     */
    protected function isFileOpened()
    {
        return is_resource($this->file);
    }

    /**
     * @param int $lock_type
     * @param int $lock_retries
     * @param int $retry_interval
     * @return bool
     */
    protected function tryLockFile($lock_type, $lock_retries, $retry_interval)
    {
        for($i = 0; $i < $lock_retries; $i++)
        {
            if(!flock($this->file, $lock_type | LOCK_NB))
            {
                if($i === ($lock_retries - 1)) return false;
                usleep($retry_interval);
            }
            else
            {
                break;
            }
        }

        return true;
    }

    /**
     * @throws AtomicFileException
     */
    protected function unlockFile()
    {
        $unlock = flock($this->file, LOCK_UN);
        if(!$unlock)
        {
            throw FileLockException::createForFailedToUnlockFile($this->getFilePath());
        }
    }

    /**
     * Checks the file whether exists and is readable or writable
     * @param bool $writable
     * @throws AtomicFileException
     */
    protected function checkFile($writable = true)
    {
        if(!$this->fileExists()) throw NonExistentFileException::createForFileDoesNotExists($this->getFilePath());
        if(!is_readable($this->file_path)) throw NotReadableFileException::createForNotReadable($this->getFilePath());
        if($writable)
        {
            if(!is_writable($this->file_path)) throw NotWritableFileException::createForNotWritable($this->getFilePath());
        }
    }

    /**
     * Checks whether file exists
     * @return bool
     */
    protected function fileExists()
    {
        $this->clearStatCache(true);
        return (file_exists($this->file_path) && is_file($this->file_path));
    }

    /**
     * Clear cache
     * @param bool $all_files If true, it clears all files
     */
    protected function clearStatCache($all_files = false)
    {
        if(version_compare(PHP_VERSION, '5.3.0') >= 0)
        {
            if($all_files === true)
            {
                clearstatcache(true);
                return;
            }

            clearstatcache(true, $this->file_path);
            return;
        }
        clearstatcache();
    }

    public function __destruct()
    {
        $this->closeFile();
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }

    /**
     * @return resource
     */
    protected function getFile()
    {
        return $this->file;
    }

    /**
     * @param resource $file
     */
    protected function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return int
     */
    public function getLockRetries()
    {
        return $this->lock_retries;
    }

    /**
     * @return int
     */
    public function getRetryInterval()
    {
        return $this->retry_interval;
    }
}