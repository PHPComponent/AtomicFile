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
class AtomicFileReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testReadFile()
    {
        $string = $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath());
        $this->assertSame($string, $file_reader->readFile());
        $file_reader->closeFile();
    }

    public function testReadFileLine()
    {
        $string = "foo\nbar\nbaz";
        $this->createTestFile($string);
        $file_reader = new AtomicFileReader($this->getFilePath());
        $this->assertSame("foo\n", $file_reader->readFileLine());
        $this->assertSame("ba", $file_reader->readFileLine(3));
        $this->assertSame("r\n", $file_reader->readFileLine());
        $this->assertSame("baz", $file_reader->readFileLine());
        $file_reader->closeFile();
    }

    public function testGetWriter()
    {
        $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath());
        $this->assertInstanceOf(AtomicFileWriter::class, $file_reader->getWriter());
    }

    /**
     * @expectedException \PHPComponent\AtomicFile\Exceptions\ReadOnlyFileException
     */
    public function testGetWriterWhenReadOnly()
    {
        $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath(), AtomicFileReader::OPEN_READ_ONLY);
        $file_reader->getWriter();
    }

    /**
     * @expectedException \PHPComponent\AtomicFile\Exceptions\NonExistentFileException
     */
    public function testOpenNonExistentFile()
    {
        $file_reader = new AtomicFileReader(dirname(__FILE__).'/../tmp/test2.txt');
        $file_reader->openFile();
    }

    public function testOpenFile()
    {
        $this->createTestFile();
        $file_reader = new AtomicFileReader($this->getFilePath());
        $this->assertInstanceOf(AtomicFileReader::class, $file_reader->openFile());
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