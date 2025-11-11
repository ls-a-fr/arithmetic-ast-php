<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from pixels to points
 */
class PixelPointConverter extends Converter
{
    public const DPI = 96;

    public static function getUnitFrom(): string
    {
        return 'pt';
    }

    public static function getUnitTo(): string
    {
        return 'px';
    }

    public function convertFrom(float $pt): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pt * (self::DPI / 72);
    }

    public function convertTo(float $px): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $px * (self::DPI / 96);
    }
}
