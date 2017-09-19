<?php
/*
 * This file is part of PHPComponent/AtomicFile.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace PHPComponent\AtomicFile\Tests;

use PHPComponent\AtomicFile\AtomicFileReader;
use PHPComponent\AtomicFile\AtomicFileWriter;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class AtomicFileWriterTest extends \PHPUnit_Framework_TestCase
{

    /** @var AtomicFileWriter */
    private $file_writer;

    public function setUp()
    {
        $this->file_writer = new AtomicFileWriter(dirname(__FILE__).'/tmp/test.txt', true);
    }

    /**
     * @expectedException \PHPComponent\AtomicFile\Exceptions\NonExistentFileException
     */
    public function testOpenNonExistentFile()
    {
        $writer = new AtomicFileWriter(dirname(__FILE__).'/../tmp/test.txt');
        $writer->openFile();
    }

    public function testOpenFile()
    {
        $this->assertInstanceOf(AtomicFileWriter::class, $this->file_writer->openFile());
    }

    public function testWriteToFile()
    {
        $this->assertSame(0, $this->file_writer->writeToFile(''));
        $this->assertSame(5, $this->file_writer->writeToFile('Hello'));
        $this->assertSame('Hello', $this->file_writer->getReader()->readFile());
    }

    public function testTruncateFile()
    {
        $this->assertSame(5, $this->file_writer->writeToFile('Hello'));
        $this->assertInstanceOf(AtomicFileWriter::class, $this->file_writer->truncateFile());
        $this->assertSame(0, $this->file_writer->getFileSize());
        $this->assertSame(5, $this->file_writer->writeToFile('Hello'));
        $this->assertInstanceOf(AtomicFileWriter::class, $this->file_writer->truncateFile(3));
        $this->assertSame(3, $this->file_writer->getFileSize());
    }

    public function testGetReader()
    {
        $this->assertInstanceOf(AtomicFileReader::class, $this->file_writer->getReader());
    }

    protected function tearDown()
    {
        $this->file_writer->closeFile();
    }
}