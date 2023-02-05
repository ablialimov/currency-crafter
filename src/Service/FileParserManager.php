<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FileParserInterface;

class FileParserManager
{
    private array $parsersMap;

    public function __construct($parsers)
    {
        /** @var FileParserInterface $parser */
        foreach ($parsers as $parser) {
            $this->parsersMap[$parser->mimeType()] = $parser;
        }
    }

    public function parse(string $filename): \Generator
    {
        return $this->getParser($filename)->parse($filename);
    }

    private function getFileMimeType(string $filename): string
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);
    }

    private function getParser(string $filename): FileParserInterface
    {
        return $this->parsersMap[$this->getFileMimeType($filename)];
    }
}
