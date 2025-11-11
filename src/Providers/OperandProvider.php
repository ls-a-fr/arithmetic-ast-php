<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Providers;

use Lsa\Arithmetic\Ast\Exceptions\InvalidOperandTokenException;
use Lsa\Arithmetic\Ast\Operands\Operand;

/**
 * An OperandProvider provides all registered operators
 *
 * @extends Provider<Operand>
 */
class OperandProvider extends Provider
{
    public static function isRegisteredFromSymbol(string $symbol): bool
    {
        foreach (self::get() as $op) {
            if ($op::getOperator() === $symbol) {
                return true;
            }
        }

        return false;
    }

    public static function makeFromSymbol(string $symbol): Operand
    {
        foreach (self::get() as $op) {
            if ($op::getOperator() === $symbol) {
                return new $op();
            }
        }
        throw new InvalidOperandTokenException('Cannot find operand from symbol: '.$symbol);
    }

    public static function getClassFromSymbol(string $symbol): string
    {
        foreach (self::get() as $op) {
            if ($op::getOperator() === $symbol) {
                return $op;
            }
        }
        throw new InvalidOperandTokenException('Cannot find operand from symbol: '.$symbol);
    }

    public static function getSymbols(): array
    {
        return array_map(fn ($cls) => $cls::getOperator(), self::get());
    }
}
