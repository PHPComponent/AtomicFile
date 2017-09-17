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
use PHPComponent\AtomicFile\Exceptions\WriteOnlyFileException;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
interface IAtomicFileWriter extends IAtomicFileHandler
{

    /**
     * @param string|int|bool $text
     * @return int
     * @throws FileOperationException
     * @throws \InvalidArgumentException
     */
    public function writeToFile($text);

    /**
     * @param int $truncate_size
     * @return $this
     * @throws FileOperationException
     */
    public function truncateFile($truncate_size = 0);

    /**
     * Return reader
     * @param int $lock_retries
     * @param int $retry_interval
     * @return IAtomicFileReader
     * @throws WriteOnlyFileException
     */
    public function getReader($lock_retries = self::LOCK_RETRIES, $retry_interval = self::RETRY_INTERVAL);
}