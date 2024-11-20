<?php

declare(strict_types=1);

namespace App\SqlGenerator;

interface IOutput
{
    public function prepare(): void;

    public function write(string $string): void;

    public function finish(): void;
}
