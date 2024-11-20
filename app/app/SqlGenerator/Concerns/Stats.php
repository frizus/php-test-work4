<?php

namespace App\SqlGenerator\Concerns;

trait Stats
{
    protected array $stats = [];

    protected function initStats(): void
    {
        foreach ($this->statsKeys() as $statKey) {
            $this->stats[$statKey] = 0;
        }
    }

    protected function statsKeys(): array
    {
        return [];
    }

    protected function incrStat($name, $incrBy = 1): void
    {
        $this->stats[$name] ??= 0;
        $this->stats[$name] += $incrBy;
    }

    public function getImportStats(): array
    {
        return $this->stats;
    }
}
