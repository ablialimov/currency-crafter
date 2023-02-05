<?php

namespace App\Service;

use App\Contract\FileParserInterface;

class FileParserManager
{
    private array $parsersMap;

    public function __construct($parsers)
    {
        /** @var FileParserInterface $parser */
        foreach ($parsers as $parser) {
            $this->parsersMap[$parser->format()] = $parser;
        }
    }

    public function parse(string $filename): array
    {
        return $this->getParser($filename)->parse($filename);
    }

    private function getFileExtension(string $filename): string
    {
        // In general better to use some lib to identify file type properly
        $fileNameParts = explode('.', $filename);

        return end($fileNameParts);
    }

    private function getParser(string $filename): FileParserInterface
    {
        return $this->parsersMap[$this->getFileExtension($filename)];
    }
}
