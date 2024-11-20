<?php

declare(strict_types=1);

namespace App\SourceReaders;

interface ICanUseFilePathSourceReader
{
    public function setFilePath(string $filePath): void;
}
