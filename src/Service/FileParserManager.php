<?php

namespace App\Service;

use App\Contract\FileParserInterface;
use App\Service\CommissionFeeCalculator;

class FileParserManager
{
    public function __construct(
        private readonly iterable $parsers,
        private readonly CommissionFeeCalculator $feeCalculator
    ) {
    }

    public function parse(string $filename): array
    {
        $ext = $this->getFileExtension($filename);

        /** @var FileParserInterface $parser */
        foreach ($this->parsers as $parser) {
            if ($ext === $parser->format()) {
                $data = $parser->parse($filename);
            }
       }

        if (empty($data)) {
            throw new \RuntimeException('Unsupported file extension.');
        }

        return $this->feeCalculator->calculate($data);
    }

    private function getFileExtension($filename): string
    {
        $fileNameParts = explode('.', $filename);

        return end($fileNameParts);
    }
}
