<?php

namespace App\Contract;

interface FileParserInterface
{
    public function format(): string;

    public function parse(string $filepath): array;
}
