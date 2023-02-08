<?php

namespace App\Tests\Service\Parsers;

use App\Service\Parsers\CsvParser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CsvParserTest extends KernelTestCase
{
    public function testParse(): void
    {
        self::bootKernel();

        self::bootKernel();

        $fsMock = vfsStream::setup();
        $fileMock = new vfsStreamFile('test.csv');
        $fsMock->addChild($fileMock);

        $parser = new CsvParser();

        $this->assertInstanceOf(\Generator::class, $parser->parse('test.csv'));
    }
}
