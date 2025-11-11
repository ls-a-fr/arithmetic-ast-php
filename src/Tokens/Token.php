<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tokens;

use Lsa\Arithmetic\Ast\Exceptions\TokenTraversalException;
use Lsa\Arithmetic\Ast\UnitConverter;

/**
 * A Token from this Abstract Syntax Tree
 */
abstract class Token
{
    /**
     * Normalized unit, if used
     */
    private static string $normalizedUnit = 'pt';

    /**
     * String version of this token
     */
    public readonly string $token;

    /**
     * Parent token, if any
     */
    private ?Token $parent = null;

    /**
     * Evaluated result for performance reasons
     */
    private string|float $evaluationResult;

    /**
     * Normalized value for performance reasons
     */
    private float $normalizedValue;

    /**
     * Evaluates this token and returns its value.
     */
    abstract public function evaluate(): float|string;

    /**
     * Returns if this token already has been evaluated.
     *
     * @return bool True if evaluated, false otherwise
     */
    protected function hasBeenEvaluated(): bool
    {
        return isset($this->evaluationResult) === true;
    }

    /**
     * Sets parent token.
     *
     * @param  Token  $token  Parent token
     *
     * @throws TokenTraversalException
     */
    protected function setParent(Token $token): void
    {
        if ($token instanceof SingleToken) {
            throw new TokenTraversalException('Cannot attach a child token to SingleToken as it has no child');
        }
        $this->parent = $token;
    }

    /**
     * Gets parent token.
     *
     * @return ?Token Parent token if found, null otherwise.
     */
    public function getParent(): ?Token
    {
        return $this->parent;
    }

    /**
     * Creates a new Token
     *
     * @param  string  $token  String version of this token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Gets RootToken by traversal
     *
     * @return RootToken The root token
     *
     * @throws TokenTraversalException
     */
    public function root(): RootToken
    {
        $current = $this;
        while ($current instanceof RootToken === false && $current !== null) {
            $current = $this->getParent();
        }
        if ($current === null) {
            throw new TokenTraversalException('Cannot find root token for token: '.$this->token);
        }

        return $current;
    }

    /**
     * Gets evaluation result
     */
    public function getEvaluationResult(): string|float
    {
        return $this->evaluationResult;
    }

    /**
     * Sets evaluation result
     *
     * @param  string|float  $value  Evaluated result
     */
    protected function setEvaluationResult(string|float $value): void
    {
        if (isset($this->evaluationResult) === false) {
            $this->evaluationResult = $value;

            $converter = UnitConverter::make();
            $value = $converter->normalize($value, self::getNormalizedUnit());
            $this->normalizedValue = $value;
        }
    }

    /**
     * Gets normalized value.
     */
    public function getNormalizedValue(): string|float
    {
        if (isset($this->normalizedValue) === false) {
            $this->evaluate();
        }

        return $this->normalizedValue;
    }

    /**
     * Sets normalized unit, if necessary
     *
     * @param  string  $normalizedUnit  Normalized unit
     */
    public static function setNormalizedUnit(string $normalizedUnit): void
    {
        self::$normalizedUnit = $normalizedUnit;
    }

    /**
     * Returns normalized unit. Defaults to 'pt'
     */
    public static function getNormalizedUnit(): string
    {
        return self::$normalizedUnit;
    }
}
