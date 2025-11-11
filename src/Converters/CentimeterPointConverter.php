<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from centimeters to points
 */
class CentimeterPointConverter extends Converter
{
    public static function getUnitFrom(): string
    {
        return 'pt';
    }

    public static function getUnitTo(): string
    {
        return 'cm';
    }

    public function convertFrom(float $pt): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pt * (25.4 / 72) * 10;
    }

    public function convertTo(float $cm): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $cm * (72 / 25.4) * 10;
    }
}
