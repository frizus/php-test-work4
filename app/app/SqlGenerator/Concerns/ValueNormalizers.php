<?php

namespace App\SqlGenerator\Concerns;

use App\Helpers\Phone;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;

trait ValueNormalizers
{
    public const string MARKET_ID_PREFIX = 'ozon:';

    public const string AUTHOR_EMPTY = 'Пользователь предпочёл скрыть свои данные';

    protected function normalizerForOzonMarketId(mixed $value): string
    {
        return self::MARKET_ID_PREFIX . $value;
    }

    protected function normalizerForAuthor(mixed $value): string
    {
        return $value === self::AUTHOR_EMPTY
            ? ''
            : (string)$value;
    }

    protected function normalizerForRussianDatetime(mixed $value): ?string
    {
        try {
            $value = Carbon::createFromLocaleIsoFormat('D MMMM Y', 'ru', $value);
        } catch (InvalidFormatException $exception) {
            $value = null;
        }

        if ($value) {
            $value->setTime(0, 0);
        }

        return $value ?: null;
    }

    protected function normalizerForMedia(mixed $value): string
    {
        $value = (string)$value;

        if (!$value || $value === '[]') {
            return '';
        }

        $value = preg_replace('#(^\[)|(\]$)#', '', $value);
        $photos = preg_split('#\s*,\s*#', $value);

        $photos = array_map(
            fn ($photo) => preg_replace('#(^\')|(\'$)#', '', $photo),
            $photos
        );

        $photos = array_filter(
            $photos,
            fn ($photo) =>
                filter_var($photo, FILTER_VALIDATE_URL) !== false
                && preg_match('#https?://#i', $photo)
        );

        return implode(' ', $photos);
    }

    protected function normalizerForString(mixed $value): string
    {
        $value = (string)$value;

        if (strpos($value, "\r") !== false) {
            $value = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $value);
        }

        return $value;
    }

    protected function normalizerForInteger(mixed $value): string|int
    {
        return resolve_value(preg_replace('/\s/', '', $value));
    }
}
