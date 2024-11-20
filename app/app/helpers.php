<?php

function validate_int($value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false;
}

function validate_float($value): bool
{
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
}

function resolve_value($value): mixed
{
    if (validate_int($value)) {
        $value = (int)$value;
    } elseif (validate_float($value)) {
        $value = (float)$value;
    }

    return $value;
}
