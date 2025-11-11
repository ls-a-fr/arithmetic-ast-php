<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tokens;

use Lsa\Arithmetic\Ast\Exceptions\InvalidFunctionTokenException;
use Lsa\Arithmetic\Ast\Functions\Function_;

/**
 * A Function is a token applying an operation: "rand".
 */
class FunctionToken extends Token
{
    /**
     * Children tokens
     *
     * @var list<Token>
     */
    public readonly array $children;

    /**
     * Function we refer to
     */
    public readonly Function_ $function;

    /**
     * Validators for function arguments
     *
     * @var array<string,callable(string|float|null):bool>
     */
    private static array $argumentValidators = [];

    /**
     * Get registered argument validators.
     *
     * @return array<string,callable(string|float|null):bool>
     */
    protected static function getArgumentValidators(): array
    {
        if (\in_array(Function_::TYPE_STRING, self::$argumentValidators) === false) {
            self::addArgumentValidator(Function_::TYPE_STRING, fn () => true);
        }
        if (\in_array(Function_::TYPE_NUMERIC, self::$argumentValidators) === false) {
            self::addArgumentValidator(Function_::TYPE_NUMERIC, fn ($value) => \is_numeric($value));
        }

        return self::$argumentValidators;
    }

    /**
     * Adds an argument validator, based on a type name and a function.
     *
     * @param  string  $name  Type name
     * @param  callable(string|float|null):bool  $validation  Validation function
     */
    public static function addArgumentValidator(string $name, callable $validation): void
    {
        self::$argumentValidators[$name] = $validation;
    }

    /**
     * Adds several argument validators, based on a type name and a function.
     *
     * @param  array<string,callable(string|float|null):bool>  $validators  Validators functions
     */
    public static function addArgumentValidators(array $validators): void
    {
        foreach ($validators as $name => $validation) {
            self::addArgumentValidator($name, $validation);
        }
    }

    /**
     * Checks if referenced function allows a specific character
     *
     * @param  string  $name  Type name
     * @param  string  $character  Character to check
     */
    public static function allowCharacter(string $name, string $character): bool
    {
        if (isset(self::getArgumentValidators()[$name]) === false) {
            return false;
        }

        return self::getArgumentValidators()[$name]($character);
    }

    /**
     * Creates a new FunctionToken
     *
     * @param  Function_  $function  Referenced function
     * @param  list<Token>  $children  Argument tokens
     */
    public function __construct(Function_ $function, array $children)
    {
        parent::__construct($function::getFunctionName());

        $function->setReference($this);
        $this->function = $function;

        foreach ($children as $child) {
            $child->setParent($this);
        }
        $this->children = $children;
    }

    /**
     * Validates argument count. Returns nothing, but throws an Exception on error.
     *
     * @throws InvalidFunctionTokenException If argument count does not match
     */
    protected function validateArgumentCount(): void
    {
        $parameters = $this->function->getParameters();
        $minParameters = count(
            array_filter(
                $parameters,
                // @phpstan-ignore function.alreadyNarrowedType
                fn ($v) => \is_array($v)
                    && \count($v) === 1
                    && \array_key_exists(Function_::MODE_REQUIRED, $v)
            )
        );
        $maxParameters = count($parameters);

        if (count($this->children) < $minParameters || count($this->children) > $maxParameters) {
            throw new InvalidFunctionTokenException(
                sprintf(
                    'Function %s allows between %d and %d parameters, %d given',
                    $this->function->getFunctionName(),
                    $minParameters,
                    $maxParameters,
                    count($this->children)
                )
            );
        }
    }

    /**
     * Evaluates arguments.
     *
     * @return list<string|float> Arguments
     */
    protected function getArguments(): array
    {
        return array_map(fn ($c) => $c->evaluate(), $this->children);
    }

    /**
     * Validate arguments' types.Returns nothing, but throws an Exception on error.
     *
     * @throws InvalidFunctionTokenException If arguments' types does not match
     */
    protected function validateArgumentTypes(): void
    {
        $parameters = $this->function->getParameters();
        $arguments = $this->getArguments();

        foreach ($arguments as $argumentIndex => $argumentValue) {
            $parameterDefinition = $parameters[$argumentIndex];
            // @phpstan-ignore function.alreadyNarrowedType, identical.alwaysTrue
            if (\is_array($parameterDefinition) === true && count($parameterDefinition) !== 1) {
                throw new InvalidFunctionTokenException(
                    'Function parameters has invalid parameter definition'
                );
            }
            $parameterType = \array_values($parameterDefinition)[0];
            // @phpstan-ignore identical.alwaysFalse
            if ($parameterType === null) {
                continue;
            }
            // @phpstan-ignore function.impossibleType, identical.alwaysFalse
            if (\is_callable($parameterType) === true) {
                if ($parameterType($argumentValue) === false) {
                    throw new InvalidFunctionTokenException(
                        sprintf(
                            'Function %s argument #%d has invalid type, %s given',
                            $this->function->getFunctionName(),
                            $argumentIndex,
                            $argumentValue
                        )
                    );
                }
            }
            // @phpstan-ignore function.alreadyNarrowedType, identical.alwaysTrue
            if (\is_string($parameterType) === true) {
                if (isset(self::getArgumentValidators()[$parameterType]) === false) {
                    \trigger_error('Could not detect validation for type '.$parameterType.'. Skipping.');

                    continue;
                }
                if (self::getArgumentValidators()[$parameterType]($argumentValue) === false) {
                    throw new InvalidFunctionTokenException(
                        sprintf(
                            'Function %s argument #%d has invalid type, %s given',
                            $this->function->getFunctionName(),
                            $argumentIndex,
                            $argumentValue
                        )
                    );
                }
            }
        }
    }

    public function evaluate(): string|float
    {
        if ($this->hasBeenEvaluated() === false) {
            $this->validateArgumentCount();
            $this->validateArgumentTypes();

            $arguments = $this->getArguments();
            $this->setEvaluationResult($this->function->evaluate(...$arguments));
        }

        return $this->getEvaluationResult();
    }
}
