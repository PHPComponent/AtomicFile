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

use PHPComponent\AtomicFile\AtomicFileHandler;

/**
 * @author František Šitner <frantisek.sitner@gmail.com>
 */
class AtomicFileHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getInvalidFilePath
     * @param $file_path
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArguments($file_path)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($file_path)
        );
    }

    public function getInvalidFilePath()
    {
        return array(
            array(''),
            array(false),
            array(true),
            array(0),
            array(12.3),
            array(null),
        );
    }

    public function testRewindFile()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($this->getFilePath())
        );

        $this->createTestFile($file);

        $mock->expects($this->at(0))
            ->method('openFile')
            ->willReturn($mock);

        $this->invokeSetFile($mock, $file);

        $this->assertSame(11, $mock->getFilePointer());
        $this->assertSame($mock, $mock->rewindFile());
        $this->assertSame(0, $mock->getFilePointer());
        $mock->closeFile();
    }

    public function testIsFileEmpty()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($this->getFilePath())
        );

        $this->createTestFile($file, '');

        $mock->expects($this->at(0))
            ->method('openFile')
            ->willReturn($mock);

        $this->invokeSetFile($mock, $file);

        $this->assertTrue($mock->isFileEmpty());
        $mock->closeFile();
    }

    public function testGetFilePointer()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($this->getFilePath())
        );

        $string = 'Test string';
        $this->createTestFile($file, $string);

        $mock->expects($this->at(0))
            ->method('openFile')
            ->willReturn($mock);

        $this->invokeSetFile($mock, $file);

        $this->assertSame(strlen($string), $mock->getFilePointer());
        $mock->closeFile();
    }

    public function testSeekFile()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($this->getFilePath())
        );

        $string = 'Testing string';
        $this->createTestFile($file, $string);

        $mock->expects($this->any())
            ->method('openFile')
            ->willReturn($mock);

        $this->invokeSetFile($mock, $file);

        $this->assertSame($mock, $mock->seekFile(2));
        $this->assertSame(2, $mock->getFilePointer());
        $this->assertSame($mock, $mock->seekFile(2, SEEK_CUR));
        $this->assertSame(4, $mock->getFilePointer());
        $this->assertSame($mock, $mock->seekFile(-3, SEEK_END));
        $this->assertSame(strlen($string) - 3, $mock->getFilePointer());
        $mock->closeFile();
    }

    public function testSeekFileToEnd()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AtomicFileHandler $mock */
        $mock = $this->getMockForAbstractClass(
            AtomicFileHandler::class,
            array($this->getFilePath())
        );

        $string = 'Testing string';
        $this->createTestFile($file, $string);

        $mock->expects($this->any())
            ->method('openFile')
            ->willReturn($mock);

        $this->invokeSetFile($mock, $file);

        $this->assertSame($mock, $mock->seekFileToEnd());
        $this->assertSame(strlen($string), $mock->getFilePointer());
        $this->assertTrue($mock->isEndOfFile());
        $mock->closeFile();
    }

    protected function tearDown()
    {
        @unlink($this->getFilePath());
    }

    /**
     * @param resource $file
     * @param string $string
     * @return string
     */
    private function createTestFile(&$file, $string = 'Test string')
    {
        $file = fopen($this->getFilePath(), 'c+b');
        fwrite($file, $string);
        return $string;
    }

    /**
     * @param $mock
     * @param $file
     */
    private function invokeSetFile($mock, $file)
    {
        $method_reflection = new \ReflectionMethod($mock, 'setFile');
        $method_reflection->setAccessible(true);
        $method_reflection->invoke($mock, $file);
    }

    /**
     * @return string
     */
    private function getFilePath()
    {
        return dirname(__FILE__).'/tmp/test.txt';
    }
}