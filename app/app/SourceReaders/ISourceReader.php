<?php

declare(strict_types=1);

namespace App\SourceReaders;

interface ISourceReader
{
    public function load(): void;

    public function close(): void;

    public function chunkNextRows(): array;

    public function getColumnNames(): array;
}
