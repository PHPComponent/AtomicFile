<?php
/*
 * This file is part of PHPComponent/AtomicFile.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\AtomicFile\Test;

use PHPComponent\AtomicFile\AtomicFileReader;
use PHPComponent\AtomicFile\AtomicFileWriter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class ParallelRunningTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \PHPComponent\AtomicFile\Exceptions\FileLockException
     */
    public function testParallelOpenForWriting()
    {
        $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath());
        $file_reader->readFileLine();

        $file_writer = new AtomicFileWriter($this->getFilePath());
        $file_writer->seekFileToEnd();
    }

    public function testParallelOpenForReading()
    {
        $testing_string = $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath());
        $this->assertSame($testing_string, $file_reader->readFileLine());

        $file_reader_2 = new AtomicFileReader($this->getFilePath());
        $this->assertSame($testing_string, $file_reader_2->readFileLine());
    }

    protected function tearDown()
    {
        @unlink($this->getFilePath());
    }

    /**
     * @param string $string
     * @return string
     */
    private function createTestFile($string = 'Test string')
    {
        $file = fopen($this->getFilePath(), 'c+b');
        fwrite($file, $string);
        fclose($file);
        return $string;
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return dirname(__FILE__).'/tmp/test.txt';
    }
}
