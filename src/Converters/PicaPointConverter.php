<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Converts from pica to points
 */
class PicaPointConverter extends Converter
{
    public static function getUnitFrom(): string
    {
        return 'pt';
    }

    public static function getUnitTo(): string
    {
        return 'pc';
    }

    public function convertFrom(float $pt): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pt / 12;
    }

    public function convertTo(float $pc): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return $pc * 12;
    }
}
