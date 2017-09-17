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

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
interface IAtomicFileHandler
{

    //Default number of times to try to close the file
    const LOCK_RETRIES = 50;

    //Default sleep time after unsuccessful close
    const RETRY_INTERVAL = 100;

    /**
     * Opens file
     * @return $this
     */
    public function openFile();

    /**
     * Rewind the file
     * @return $this
     * @throws AtomicFileException
     */
    public function rewindFile();

    /**
     * Move file pointer to desired position
     * @param int $offset
     * @param int $whence
     * @return $this
     * @throws AtomicFileException
     */
    public function seekFile($offset, $whence = SEEK_SET);

    /**
     * @return $this
     * @throws AtomicFileException
     */
    public function seekFileToEnd();

    /**
     * @return bool
     * @throws AtomicFileException
     */
    public function isEndOfFile();

    /**
     * Return actual pointer of file resource
     * @return int
     * @throws AtomicFileException
     */
    public function getFilePointer();

    /**
     * @return void
     * @throws AtomicFileException
     */
    public function closeFile();

    /**
     * Find whether file is empty
     * @return bool
     * @throws AtomicFileException
     */
    public function isFileEmpty();

    /**
     * Returns actual file size
     * @return int
     */
    public function getFileSize();
}