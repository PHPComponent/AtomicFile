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
 * Thrown when file does not exists
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class NonExistentFileException extends AtomicFileException
{

    /**
     * @param string $file_path
     * @return NonExistentFileException
     */
    public static function createForFileDoesNotExists($file_path)
    {
        return new self($file_path, 'File does not exists');
    }
}