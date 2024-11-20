<?php

declare(strict_types=1);

namespace App\SourceReaders;

class AbstractSourceReader
{
    protected int $chunk = 100;

    protected ?string $fileType;

    public function setFileType(?string $fileType): void
    {
        $this->fileType = mb_strtolower((string)$fileType);
    }
}
