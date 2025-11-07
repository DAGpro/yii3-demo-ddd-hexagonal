<?php

declare(strict_types=1);

namespace App\Infrastructure;

use DateTimeInterface;
use IntlDateFormatter;

class LocaleDateFormatter
{
    public static function format(
        DateTimeInterface|string|int $datetime,
        string $locale = 'en-En',
        string $pattern = 'LLLL',
    ): string {
        $localeFormatter = new IntlDateFormatter(
            $locale,
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            $pattern,
        )->format($datetime);

        return mb_convert_case($localeFormatter, MB_CASE_TITLE);
    }
}
