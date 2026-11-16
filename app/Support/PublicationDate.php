<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use InvalidArgumentException;

class PublicationDate
{
    public const DISPLAY_TIMEZONE = 'America/Sao_Paulo';

    public const FORMAT = 'Y-m-d\TH:i';

    public const RULES = ['nullable', 'date_format:Y-m-d\TH:i'];

    public static function toDisplay(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value, 'UTC')->setTimezone(self::DISPLAY_TIMEZONE)->format(self::FORMAT);
    }

    public static function toUtc(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat(self::FORMAT, (string) $value, self::DISPLAY_TIMEZONE);
        } catch (InvalidFormatException) {
            throw new InvalidArgumentException('Data de publicação inválida.');
        }

        // createFromFormat rola datas inexistentes (2026-02-30 vira março) e só avisa; o retorno idêntico barra isso e formatos não canônicos.
        if ($date === false || CarbonImmutable::getLastErrors() !== false || $date->format(self::FORMAT) !== (string) $value) {
            throw new InvalidArgumentException('Data de publicação inválida.');
        }

        return $date->setTime($date->hour, $date->minute, 0)->utc()->format('Y-m-d H:i:s');
    }

    public static function normalize(array $validated): array
    {
        if (array_key_exists('published_at', $validated)) {
            $validated['published_at'] = self::toUtc($validated['published_at']);
        }

        return $validated;
    }
}
