<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from inches to points
 */
class InchPointConverter extends Converter
{
    public static function getUnitFrom(): string
    {
        return 'pt';
    }

    public static function getUnitTo(): string
    {
        return 'in';
    }

    public function convertFrom(float $pt): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pt / 72;
    }

    public function convertTo(float $in): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $in * 72;
    }
}
