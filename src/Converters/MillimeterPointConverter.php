<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from millimeters to points
 */
class MillimeterPointConverter extends Converter
{
    public static function getUnitFrom(): string
    {
        return 'pt';
    }

    public static function getUnitTo(): string
    {
        return 'mm';
    }

    public function convertFrom(float $pt): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pt * (25.4 / 72);
    }

    public function convertTo(float $mm): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $mm * (72 / 25.4);
    }
}
