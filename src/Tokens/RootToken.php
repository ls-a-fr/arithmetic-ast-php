<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tokens;

/**
 * A RootToken is root of Abstract Syntax Tree.
 */
class RootToken extends Token
{
    /**
     * Inner token
     */
    public readonly Token $child;

    /**
     * Creates a new RootToken
     *
     * @param  string  $token  String version of this token
     * @param  Token  $child  Child of this root token
     */
    public function __construct(string $token, Token $child)
    {
        parent::__construct($token);
        $this->child = $child;
        $this->child->setParent($this);
    }

    public function evaluate(): float|string
    {
        if ($this->hasBeenEvaluated() === false) {
            $this->setEvaluationResult($this->child->evaluate());
        }

        return $this->getEvaluationResult();
    }
}
