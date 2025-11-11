<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Operands;

/**
 * Multiply operand.
 */
class MultiplyOperand extends Operand
{
    public static function getAssociativity(): string
    {
        return Operand::LEFT_ASSOCIATIVE;
    }

    public static function getOperator(): string
    {
        return '*';
    }

    public static function getPrecedence(): int
    {
        return 2;
    }

    /**
     * Whether this operator should keep units.
     *
     * @param  string  $unit1  — First unit
     * @param  string  $unit2  — Second unit
     * @return bool — True if this operator should keep unit, false otherwise.
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
     */
    public static function shouldKeepUnit(string $unit1, string $unit2): bool
    {
        return true;
    }

    public function execute(float|string $value1, float|string $value2): float
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return \floatval($value1) * \floatval($value2);
    }
}
