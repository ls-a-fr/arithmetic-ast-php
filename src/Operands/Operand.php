<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Operands;

use Lsa\Arithmetic\Ast\Exceptions\InvalidOperationException;
use Lsa\Arithmetic\Ast\Tokens\Token;
use Lsa\Arithmetic\Ast\UnitConverter;

/**
 * Abstract representation of an operand
 */
abstract class Operand
{
    public const LEFT_ASSOCIATIVE = 'left';

    public const RIGHT_ASSOCIATIVE = 'right';

    /**
     * Fetches the operator keyword.
     */
    abstract public static function getOperator(): string;

    /**
     * Fetches the operator precedence. Higher number means higher precedence.
     */
    abstract public static function getPrecedence(): int;

    /**
     * Whether this operator should keep units.
     *
     * @param  string  $unit1  First unit
     * @param  string  $unit2  Second unit
     * @return bool True if this operator should keep unit, false otherwise.
     */
    abstract public static function shouldKeepUnit(string $unit1, string $unit2): bool;

    /**
     * Gets operator associativity
     *
     * @return Operand::LEFT_ASSOCIATIVE|Operand::RIGHT_ASSOCIATIVE
     */
    abstract public static function getAssociativity(): string;

    /**
     * Resolves this operator.
     *
     * @param  float|string  $value1  First value
     * @param  float|string  $value2  Second value
     * @return float Operation result
     */
    abstract protected function execute(float|string $value1, float|string $value2): float;

    /**
     * Whether this operator can be unary or not. Example: "+1" means "+" operator operates on
     * only one token.
     *
     * @return bool True if this operator can be unary, false otherwise. Default false.
     */
    public static function canBeUnary(): bool
    {
        return false;
    }

    /**
     * Evaluates an operand.
     *
     * @param  Token  $token1  First token
     * @param  Token  $token2  Second token
     *
     * @throws InvalidOperationException
     */
    public function evaluate(Token $token1, Token $token2): string|float
    {
        $converter = UnitConverter::make();

        // Prevent invalid operations
        $unitPower = $converter->countUnitPower($token1->evaluate());
        if ($unitPower !== $converter->countUnitPower($token2->evaluate())) {
            throw new InvalidOperationException('Unit power must be same for '.static::getOperator().' operand');
        }

        $result = static::execute(
            $token1->getNormalizedValue(),
            $token2->getNormalizedValue()
        );

        if ($unitPower === 1) {
            $unit1 = $converter->getUnit($token1->getEvaluationResult());
            $unit2 = $converter->getUnit($token2->getEvaluationResult());

            if (static::shouldKeepUnit($unit1, $unit2) === true) {
                $resultWithUnit = $result.Token::getNormalizedUnit();

                return $converter->normalize($resultWithUnit, $unit1).$unit1;
            }

            return $result;
        }

        return $result;
    }
}
