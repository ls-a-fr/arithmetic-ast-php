<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Operands;

/**
 * Divide operand.
 */
class DivideOperand extends Operand
{
    public static function getAssociativity(): string
    {
        return Operand::LEFT_ASSOCIATIVE;
    }

    public static function getOperator(): string
    {
        return '/';
    }

    public static function getPrecedence(): int
    {
        return 2;
    }

    public static function shouldKeepUnit(string $unit1, string $unit2): bool
    {
        return $unit1 !== $unit2;
    }

    public function execute(float|string $value1, float|string $value2): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return \floatval($value1) / \floatval($value2);
    }
}
