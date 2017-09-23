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
class FileOperationException extends AtomicFileException
{

    const OPERATION_OPEN_FILE = 1;
    const OPERATION_CLOSE_FILE = 2;
    const OPERATION_WRITE_TO_FILE = 3;
    const OPERATION_READ_FROM_FILE = 4;
    const OPERATION_SEEK_FILE = 5;
    const OPERATION_REWIND_FILE = 6;
    const OPERATION_TRUNCATE_FILE = 7;
    const OPERATION_COPY_INTO_TEMP_FILE = 8;

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToRewindFile($file_path)
    {
        return new self($file_path, 'Failed to rewind file', self::OPERATION_REWIND_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToSeekFile($file_path)
    {
        return new self($file_path, 'Failed to seek file', self::OPERATION_SEEK_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToOpenFile($file_path)
    {
        return new self($file_path, 'Failed to open file', self::OPERATION_OPEN_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToOpenTempFile($file_path)
    {
        return new self($file_path, 'Failed to open temp file', self::OPERATION_OPEN_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToCopyIntoTempFile($file_path)
    {
        return new self($file_path, 'Failed to copy into temp file', self::OPERATION_COPY_INTO_TEMP_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToCloseFile($file_path)
    {
        return new self($file_path, 'Failed to close file', self::OPERATION_CLOSE_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToWriteToFile($file_path)
    {
        return new self($file_path, 'Failed to write to file', self::OPERATION_WRITE_TO_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToReadFromFile($file_path)
    {
        return new self($file_path, 'Failed to read from file', self::OPERATION_READ_FROM_FILE);
    }

    /**
     * @param string $file_path
     * @return FileOperationException
     */
    public static function createForFailedToTruncateFile($file_path)
    {
        return new self($file_path, 'Failed to truncate file', self::OPERATION_TRUNCATE_FILE);
    }
}