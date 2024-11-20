<?php

declare(strict_types=1);

namespace App\SourceReaders;

use App\UnsupportedFeatureException;

class SourceReaderBuilder
{
    protected AbstractSourceReader $sourceReader;

    public function __construct(?string $fileType = null)
    {
        $fileType = mb_strtolower($fileType);
        $this->sourceReader = (new SourceReaderFactory($fileType))->getSourceReader();
        $this->setFileType($fileType);
    }

    public function build(): ISourceReader
    {
        return $this->sourceReader;
    }

    public function setFileType(?string $fileType): void
    {
        $this->sourceReader->setFileType($fileType);
    }

    /**
     * @throws UnsupportedFeatureException
     */
    public function setFilePath(string $filePath): static
    {
        if (!$this->sourceReader instanceof ICanUseFilePathSourceReader) {
            throw new UnsupportedFeatureException();
        }

        $this->sourceReader->setFilePath($filePath);

        return $this;
    }

    public function setBinary(string $binary): static
    {
        if (!$this->sourceReader instanceof ICanUseBinarySourceReader) {
            throw new UnsupportedFeatureException();
        }

        $this->sourceReader->setBinary($binary);

        return $this;
    }
}
