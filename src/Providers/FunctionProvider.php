<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Providers;

use Lsa\Arithmetic\Ast\Exceptions\InvalidFunctionTokenException;
use Lsa\Arithmetic\Ast\Functions\Function_;

/**
 * A FunctionProvider provides all registered functions
 *
 * @extends Provider<Function_>
 */
class FunctionProvider extends Provider
{
    public static function isRegisteredFromSymbol(string $symbol): bool
    {
        foreach (self::get() as $fn) {
            if ($fn::getFunctionName() === $symbol) {
                return true;
            }
        }

        return false;
    }

    public static function makeFromSymbol(string $symbol): Function_
    {
        foreach (self::get() as $fn) {
            if ($fn::getFunctionName() === $symbol) {
                return new $fn();
            }
        }
        throw new InvalidFunctionTokenException('Cannot find function from symbol: '.$symbol);
    }

    public static function getClassFromSymbol(string $symbol): string
    {
        foreach (self::get() as $fn) {
            if ($fn::getFunctionName() === $symbol) {
                return $fn;
            }
        }
        throw new InvalidFunctionTokenException('Cannot find function from symbol: '.$symbol);
    }

    public static function getSymbols(): array
    {
        return array_map(fn ($cls) => $cls::getFunctionName(), self::get());
    }
}
