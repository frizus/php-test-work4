<?php

declare(strict_types=1);

namespace App\SqlGenerator;

use Illuminate\Support\Facades\DB;

final class QueryFormer implements IQueryFormer
{
    public function formBatch(string $tableName, array $columns, array $rows): string
    {
        $sql = 'INSERT INTO `' . $tableName . "` \n  (";
        $sql .= '`' . implode('`, `', $columns) . '`';
        $sql .= ")\nVALUES\n";

        $firstRow = true;
        foreach ($rows as $row) {
            if (!$firstRow) {
                $sql .= ",\n";
            } else {
                $firstRow = false;
            }

            $sql .= '  (';

            $first = true;
            foreach ($columns as $column) {
                if (!$first) {
                    $sql .= ', ';
                } else {
                    $first = false;
                }

                $sql .= DB::escape($row[$column]);
            }

            $sql .= ")";
        }

        $sql .= ";\n";

        return $sql;
    }
}
