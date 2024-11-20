<?php

namespace App\SqlGenerator;

use Illuminate\Support\Facades\Storage;

class FileOutput implements IOutput
{
    public function __construct(
        protected string $filePath,
        protected string $diskName = 'storage_root',
    ) {
    }

    public function outputPath(): string
    {
        return Storage::disk($this->diskName)
            ->path($this->filePath);
    }

    public function prepare(): void
    {
        Storage::disk($this->diskName)
            ->delete($this->filePath);
    }

    public function write(string $string): void
    {
        Storage::disk($this->diskName)
            ->append($this->filePath, $string);
    }

    public function finish(): void
    {

    }
}
