<?php

declare(strict_types=1);

namespace App\Contract;

interface FileParserInterface
{
    public function mimeType(): string;

    public function parse(string $filepath): \Generator;
}
