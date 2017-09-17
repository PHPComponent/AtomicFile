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
 * Thrown when failed to lock or unlock file
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class FileLockException extends AtomicFileException
{

    const LOCK_FILE = 1;
    const UNLOCK_FILE = 2;

    /**
     * @param string $file_path
     * @return FileLockException
     */
    public static function createForFailedToLockFile($file_path)
    {
        return new self($file_path, 'Failed to lock file', self::LOCK_FILE);
    }

    /**
     * @param string $file_path
     * @return FileLockException
     */
    public static function createForFailedToUnlockFile($file_path)
    {
        return new self($file_path, 'Failed to unlock file', self::UNLOCK_FILE);
    }
}