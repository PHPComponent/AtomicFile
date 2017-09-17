<?php
/*
 * This file is part of PHPComponent/AtomicFile.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\AtomicFile\Exceptions;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class NotWritableFileException extends AtomicFileException
{

    /**
     * @param string $file_path
     * @return NotWritableFileException
     */
    public static function createForNotWritable($file_path)
    {
        return new self($file_path, 'File is not writable');
    }
}