<?php

declare(strict_types=1);

namespace App\Service\Parsers;

use App\Contract\FileParserInterface;

class CsvParser implements FileParserInterface
{
    public function mimeType(): string
    {
        return 'text/csv';
    }

    public function parse(string $filename): \Generator
    {
        $handle = fopen($filename, 'r');

        try {
            while ($line = fgetcsv($handle)) {
                yield $line;
            }
        } finally {
            fclose($handle);
        }
    }
}
