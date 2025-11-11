<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tokens;

use Lsa\Arithmetic\Ast\Exceptions\InvalidOperandTokenException;
use Lsa\Arithmetic\Ast\Operands\Operand;

/**
 * An OperandToken is a token applying an operation: "+", "-".
 */
class OperandToken extends Token
{
    /**
     * Children tokens
     *
     * @var list<Token>
     */
    public readonly array $children;

    /**
     * Operand this token refers to
     */
    public readonly Operand $operand;

    /**
     * Creates a new OperandToken
     *
     * @param  Operand  $operand  Operand to refer to
     * @param  list<Token>  $children  Operator arguments
     *
     * @throws InvalidOperandTokenException
     */
    public function __construct(Operand $operand, array $children)
    {
        parent::__construct($operand::getOperator());
        if (count($children) !== 2) {
            throw new InvalidOperandTokenException('OperandToken should have exactly two children');
        }

        $this->operand = $operand;
        foreach ($children as $child) {
            $child->setParent($this);
        }
        $this->children = $children;
    }

    public function evaluate(): string|float
    {
        if ($this->hasBeenEvaluated() === false) {
            $this->setEvaluationResult($this->operand->evaluate($this->children[0], $this->children[1]));
        }

        return $this->getEvaluationResult();
    }
}
