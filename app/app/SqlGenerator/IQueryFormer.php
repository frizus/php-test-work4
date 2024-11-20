<?php

declare(strict_types=1);

namespace App\SqlGenerator;

interface IQueryFormer
{
    /**
     * @param string $tableName
     * @param array[] $rows
     */
    public function formBatch(string $tableName, array $columns, array $rows): string;
}
