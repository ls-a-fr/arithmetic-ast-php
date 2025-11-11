<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Converters;

/**
 * Abstract converter class, to convert from a specific unit to another one
 */
abstract class Converter
{
    /**
     * Gets the unit this converter converts from
     */
    abstract public static function getUnitFrom(): string;

    /**
     * Gets the unit this converter converts to
     */
    abstract public static function getUnitTo(): string;

    /**
     * Converts from the second unit in the Converter name to the first.
     *
     * @example Considering a CentimeterPointConverter, `convertFrom` will
     * convert from point to centimeter
     *
     * ```php
     * <?php
     * public function convertFrom(float $pt): float
     * {
     *     return $pt * (25.4 / 72) * 10;
     * }
     * ```
     */
    abstract public function convertFrom(float $value): float;

    /**
     * Converts from the first unit in the Converter name to the second.
     *
     * @example Considering a CentimeterPointConverter, `convertTo` will
     * convert from centimeter to point
     *
     * ```php
     * <?php
     * public function convertTo(float $cm): float
     * {
     *     return $cm * (72 / 25.4) * 10;
     * }
     * ```
     */
    abstract public function convertTo(float $value): float;
}
