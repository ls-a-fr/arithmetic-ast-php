<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Functions;

use Lsa\Arithmetic\Ast\Tokens\Token;

/**
 * Abstract representation of a function
 *
 * phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid, Squiz.Classes.ValidClassName.NotPascalCase
 */
abstract class Function_
{
    public const MODE_REQUIRED = 'required';

    public const MODE_OPTIONAL = 'optional';

    public const TYPE_STRING = 'string';

    public const TYPE_NUMERIC = 'numeric';

    /**
     * Referenced token, if any
     */
    protected ?Token $referencedToken;

    /**
     * Sets token reference for further usage
     *
     * @param  Token  $token  Referenced token
     */
    public function setReference(Token $token): void
    {
        $this->referencedToken = $token;
    }

    /**
     * Gets token reference, or null if undefined
     */
    public function getReference(): ?Token
    {
        return $this->referencedToken;
    }

    /**
     * Returns this function name (as a keyword).
     */
    abstract public static function getFunctionName(): string;

    /**
     * Gets this function parameters
     *
     * @return list<array<static::MODE_*,static::TYPE_*>>
     */
    abstract public static function getParameters(): array;

    /**
     * Evaluates this function and returns a result based on given tokens.
     *
     * @param  string|float  ...$args  Arguments for this function
     * @return string|float Evaluation result
     */
    abstract public function evaluate(...$args): string|float;
}
