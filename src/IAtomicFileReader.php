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

use PHPComponent\AtomicFile\Exceptions\FileOperationException;
use PHPComponent\AtomicFile\Exceptions\ReadOnlyFileException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
interface IAtomicFileReader extends IAtomicFileHandler
{

    /**
     * Read the file
     * @return string
     * @throws FileOperationException
     */
    public function readFile();

    /**
     * Read line from file
     * @param null|int $length
     * @return bool|string
     */
    public function readFileLine($length = null);

    /**
     * Return writer
     * @param int $lock_retries
     * @param int $retry_interval
     * @return IAtomicFileWriter
     * @throws ReadOnlyFileException
     */
    public function getWriter($lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL);
}