<?php

declare(strict_types=1);

namespace App\SourceReaders;

class SourceReaderFactory
{
    protected ISourceReader $sourceReader;

    public function __construct(?string $fileType = null)
    {
        $this->sourceReader = match ($fileType) {
            default => new PhpSpreadSheetSourceReader(),
        };
    }

    public function getSourceReader(): ISourceReader
    {
        return $this->sourceReader;
    }
}
