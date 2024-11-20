<?php

declare(strict_types=1);

namespace App\SourceReaders;

interface ICanUseBinarySourceReader
{
    public function setBinary(string $binary): void;
}
