<?php

declare(strict_types=1);

namespace App\SqlGenerator;

use App\SqlGenerator\Concerns\Stats;
use App\SqlGenerator\Concerns\ValueNormalizers;

abstract class AbstractSqlGenerator
{
    use Stats;
    use ValueNormalizers;

    protected const array MAP = [];

    protected const array FIELD_NORMALIZERS = [];

    protected const array EXTRA_SQL_FIELDS_WITH_DEFAULT_VALUES = [];

    protected function normalizeRowsValues(array &$rows): void
    {
        foreach ($rows as &$row) {
            foreach ($row as $fieldName => $value) {
                $row[$fieldName] = $this->getNormalizedValue(
                    $value,
                    $fieldName
                );
            }
        }
    }

    protected function getNormalizedValue(mixed $value, string $name): mixed
    {
        if (!key_exists($name, static::FIELD_NORMALIZERS)) {
            return $value;
        }

        $normalizerName = static::FIELD_NORMALIZERS[$name];

        return $this->{'normalizerFor' . $normalizerName}($value);
    }
}
