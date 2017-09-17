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
abstract class AtomicFileException extends \Exception
{

    /** @var string */
    private $file_path;

    /**
     * AtomicFileException constructor.
     * @param string $file_path
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($file_path, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->file_path = $file_path;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->file_path;
    }
}
