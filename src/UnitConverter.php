<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast;

use Lsa\Arithmetic\Ast\Converters\CentimeterPointConverter;
use Lsa\Arithmetic\Ast\Converters\Converter;
use Lsa\Arithmetic\Ast\Converters\InchPointConverter;
use Lsa\Arithmetic\Ast\Converters\MillimeterPointConverter;
use Lsa\Arithmetic\Ast\Converters\PercentageFloatConverter;
use Lsa\Arithmetic\Ast\Converters\PicaPointConverter;
use Lsa\Arithmetic\Ast\Converters\PixelPointConverter;
use Lsa\Arithmetic\Ast\Exceptions\InvalidFunctionTokenException;

/**
 * Allows to convert values between units.
 */
final class UnitConverter
{
    /**
     * Current instance
     */
    private static UnitConverter $instance;

    /**
     * Available converters
     *
     * @var array<string, Converter>
     */
    private array $availableConverters = [];

    /**
     * Returns available units, if any.
     *
     * @return list<string>
     */
    public function getAvailableUnits(): array
    {
        $fromUnits = array_values(
            array_map(fn ($c) => $c::getUnitFrom(), $this->getAvailableUnitConverters())
        );
        $toUnits = array_values(
            array_map(fn ($c) => $c::getUnitTo(), $this->getAvailableUnitConverters())
        );

        return \array_values(array_unique([...$fromUnits, ...$toUnits]));
    }

    /**
     * Adds a converter. A converter transforms an unit value to another unit value.
     *
     * @param  Converter  $converter  Converter to be added
     */
    public function addConverter(Converter $converter): void
    {
        $this->availableConverters[\get_class($converter)] = $converter;
    }

    /**
     * Count unit power in this value.
     *
     * Some examples:
     * - `countUnitPower("1")` returns 0, as `1` is zero-power unit.
     * - `countUnitPower("1cm")` returns 1, as `1cm` is one-power unit.
     * - `countUnitPower("1cm²")` returns 2, as `1cm²` is two-power unit.
     *
     * @return int<0, 1>
     */
    public function countUnitPower(string|float $value): int
    {
        if ($this->hasUnit($value) === false) {
            return 0;
        }

        $unitlessValue = $this->stripUnit(\strval($value));
        if (\strval($unitlessValue) === $value) {
            return 0;
        }

        return 1;
    }

    /**
     * Returns available unit converters.
     *
     * @return array<string, Converter>
     */
    public function getAvailableUnitConverters(): array
    {
        return $this->availableConverters;
    }

    /**
     * Get access to UnitConverter.
     */
    public static function make(): UnitConverter
    {
        if (isset(self::$instance) === false) {
            self::$instance = new self();
            $defaultConverterClasses = [
                CentimeterPointConverter::class,
                InchPointConverter::class,
                MillimeterPointConverter::class,
                PercentageFloatConverter::class,
                PicaPointConverter::class,
                PixelPointConverter::class,
            ];
            foreach ($defaultConverterClasses as $defaultConverterClass) {
                self::$instance->addConverter(new $defaultConverterClass());
            }
        }

        return self::$instance;
    }

    /**
     * Casts a value (considered that it has no unit)
     *
     * @return float The casted value
     */
    protected function cast(string $value): float
    {
        return \floatval($value);
    }

    /**
     * Removes unit from specified value
     *
     * @return float The casted value
     */
    public function stripUnit(string $value): float
    {
        if ($this->hasUnit($value) === false) {
            return $this->cast($value);
        }

        return $this->cast(substr($value, 0, (-1 * \strlen($this->getUnit($value)))));
    }

    /**
     * Checks registered units to find if the specified value has an unit.
     *
     * @return bool True if this value has an unit, false otherwise.
     */
    public function hasUnit(string|float $value): bool
    {
        if (\is_string($value) === false) {
            return false;
        }
        try {
            $this->getUnit($value);

            return true;
        } catch (InvalidFunctionTokenException) {
            return false;
        }
    }

    /**
     * Gets the specified value unit. Throws an exception if value is a float.
     *
     * @return string Value's unit
     *
     * @throws InvalidFunctionTokenException
     */
    public function getUnit(float|string $value): string
    {
        if (\is_float($value) === true) {
            throw new InvalidFunctionTokenException('Cannot get unit for a float value: '.$value);
        }
        foreach (self::getAvailableUnits() as $unit) {
            if ($unit === '') {
                continue;
            }
            if (\str_ends_with($value, $unit) === true) {
                return $unit;
            }
        }
        throw new InvalidFunctionTokenException('Cannot get unit for token: '.$value);
    }

    /**
     * Normalize a value to a target unit.
     *
     * @param  string|float  $value  Value to normalize
     * @param  string  $targetUnit  Targetted unit
     *
     * @throws InvalidFunctionTokenException
     */
    public function normalize(string|float $value, string $targetUnit): float
    {
        $unit = '';
        if ($this->hasUnit($value) === true) {
            $unit = $this->getUnit(\strval($value));
        }
        if ($unit === $targetUnit) {
            return $this->cast(\strval($value));
        }

        if (\is_string($value) === true) {
            $value = $this->cast($value);
        }
        [$converter, $action] = $this->findConverterAction($unit, $targetUnit);
        if ($converter === null) {
            if ($unit === '') {
                return $value;
            }
            throw new InvalidFunctionTokenException('Invalid unit found: '.$unit);
        }

        return $converter->{$action}($value);
    }

    /**
     * Finds a converter action.
     *
     * @param  string  $unit  Unit to convert from
     * @param  string  $targetUnit  Unit to convert to
     * @return array{0:?Converter,1:string}
     */
    protected function findConverterAction(string $unit, string $targetUnit): array
    {
        foreach ($this->availableConverters as $converter) {
            if ($converter::getUnitFrom() === $unit && $converter::getUnitTo() === $targetUnit) {
                return [$converter, 'convertFrom'];
            }
            if ($converter::getUnitTo() === $unit && $converter::getUnitFrom() === $targetUnit) {
                return [$converter, 'convertTo'];
            }
        }

        return [null, ''];
    }
}
