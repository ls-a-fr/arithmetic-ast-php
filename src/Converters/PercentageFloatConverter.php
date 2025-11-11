<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from percentage to points
 */
class PercentageFloatConverter extends Converter
{
    public static function getUnitFrom(): string
    {
        return '';
    }

    public static function getUnitTo(): string
    {
        return '%';
    }

    public function convertFrom(float $percentage): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $percentage / 100;
    }

    public function convertTo(float $float): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $float * 100;
    }
}
