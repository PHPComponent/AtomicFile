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
use PHPComponent\AtomicFile\Exceptions\ReadOnlyFileException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class AtomicFileReader extends AtomicFileHandler implements IAtomicFileReader
{

    const OPEN_READ_ONLY = true;
    const OPEN_NOT_READ_ONLY = false;

    /** @var bool */
    private $read_only;

    /**
     * @param string $file_path
     * @param bool $read_only
     * @param int $lock_retries
     * @param int $retry_interval
     * @throws \InvalidArgumentException
     */
    public function __construct($file_path, $read_only = self::OPEN_NOT_READ_ONLY, $lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL)
    {
        $this->read_only = $read_only;
        parent::__construct($file_path, $lock_retries, $retry_interval);
    }

    /**
     * @return $this
     * @throws AtomicFileException
     */
    public function openFile()
    {
        if($this->isFileOpened()) return $this;
        $this->checkFile(false);
        $this->setFile(fopen($this->getFilePath(), $this->getOpenMode()));
        if(!is_resource($this->getFile()))
        {
            throw FileOperationException::createForFailedToOpenFile($this->getFilePath());
        }
        if(!$this->tryLockFile(LOCK_SH, $this->getLockRetries(), $this->getRetryInterval()))
        {
            throw FileLockException::createForFailedToLockFile($this->getFilePath());
        }
        return $this;
    }

    /**
     * Read the file
     * @return string
     * @throws FileOperationException
     */
    public function readFile()
    {
        $this->openFile();
        $length = filesize($this->getFilePath());
        if($length === 0) return '';
        $read = fread($this->getFile(), $length);
        if($read === false)
        {
            throw FileOperationException::createForFailedToReadFromFile($this->getFilePath());
        }
        return $read;
    }

    /**
     * Read line from file
     * @param null|int $length
     * @return bool|string
     */
    public function readFileLine($length = null)
    {
        $this->openFile();
        //gets warning "fgets(): Length parameter must be greater than 0" when parameter is null and is passed to function
        if($length === null)
        {
            $line = fgets($this->getFile());
        }
        else
        {
            $line = fgets($this->getFile(), $length);
        }

        return $line;
    }

    /**
     * Return writer
     * @param int $lock_retries
     * @param int $retry_interval
     * @return IAtomicFileWriter
     * @throws ReadOnlyFileException
     */
    public function getWriter($lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL)
    {
        if($this->read_only === self::OPEN_READ_ONLY)
        {
            throw ReadOnlyFileException::createForReadOnly($this->getFilePath());
        }
        $this->openFile();
        $writer = new AtomicFileWriter($this->getFilePath(), false, $lock_retries, $retry_interval);
        $this->closeFile();
        return $writer->openFile();
    }

    /**
     * @return string
     */
    protected function getOpenMode()
    {
        return 'rb';
    }
}