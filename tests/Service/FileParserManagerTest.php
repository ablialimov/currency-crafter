<?php

namespace App\Tests\Service;

use App\Service\FileParserManager;
use App\Service\Parsers\CsvParser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FileParserManagerTest extends KernelTestCase
{
    public function testParse(): void
    {
        self::bootKernel();

        $fsMock = vfsStream::setup();
        $fileMock = new vfsStreamFile('test.csv');
        $fsMock->addChild($fileMock);

        $csvParser = $this->createMock(CsvParser::class);
        $csvParser->method('parse');
        $csvParser->method('mimeType')->willReturn('text/csv');

        $manager = new FileParserManager(['text/csv' => $csvParser]);

        $this->assertInstanceOf(\Generator::class, $manager->parse('test.csv'));
    }
}
