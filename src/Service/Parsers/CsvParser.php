<?php

namespace App\Service\Parsers;

use App\Contract\FileParserInterface;

class CsvParser implements FileParserInterface
{
    public function format(): string
    {
        return 'csv';
    }

    public function parse(string $filepath): array
    {
        $result = [];

        if (($handle = fopen($filepath, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $result[] = $data;
            }

            fclose($handle);
        }

        return $result;
    }
}
