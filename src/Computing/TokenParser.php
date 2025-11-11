<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Computing;

use Lsa\Arithmetic\Ast\Exceptions\NoOperationParserException;
use Lsa\Arithmetic\Ast\Exceptions\ParserErrorException;
use Lsa\Arithmetic\Ast\Providers\FunctionProvider;
use Lsa\Arithmetic\Ast\Providers\OperandProvider;
use Lsa\Arithmetic\Ast\Tokens\FunctionToken;
use Lsa\Arithmetic\Ast\Tokens\OperandToken;
use Lsa\Arithmetic\Ast\Tokens\RootToken;
use Lsa\Arithmetic\Ast\Tokens\SingleToken;
use Lsa\Arithmetic\Ast\Tokens\Token;

/**
 * Represents a parser for operations.
 *
 * An operation consists of terms and operands.
 * Operands can be:
 * - `+`
 * - `-`
 * - `*`
 * - `div`
 * - `mod`
 * - Some function. ex: `floor`
 *
 * Terms can be :
 * - a value with one unit (one power unit): 1cm
 * - a value without unit (zero power unit): 1
 * - parentheses containing another operation: (2in + 1in)
 *
 * @phpstan-consistent-constructor
 */
class TokenParser
{
    /**
     * TokenParser instance
     */
    private static TokenParser $instance;

    /**
     * Configure this token parser
     *
     * phpcs:disable Generic.Functions.OpeningFunctionBraceBsdAllman.BraceOnSameLine, PEAR.Functions.FunctionDeclaration.BraceOnSameLine
     */
    public static function configure(TokenParser $parser): void {}

    /**
     * Makes a new TokenParser
     */
    public static function make(): TokenParser
    {
        if (isset(self::$instance) === false) {
            self::$instance = new static();
            static::configure(self::$instance);
        }

        return self::$instance;
    }

    /**
     * Creates a new TokenParser.
     *
     * phpcs:disable Generic.Functions.OpeningFunctionBraceBsdAllman.BraceOnSameLine, PEAR.Functions.FunctionDeclaration.BraceOnSameLine
     */
    protected function __construct() {}

    /**
     * Parses a string (called a token).
     *
     * @param  string  $token  String to parse
     * @return RootToken An instance of RootToken, containing the abstract syntax tree.
     */
    public static function parse(string $token): RootToken
    {
        $parser = new TokenParser();
        $segments = $parser->doParse($token);

        return new RootToken($token, $parser->castTokens($segments));
    }

    /**
     * Cast tokens in Token objects
     *
     * @param  list<string>  $children  Children found by `doParse`
     *
     * @throws ParserErrorException
     * @throws NoOperationParserException
     */
    protected function castTokens(array $children): Token
    {
        /**
         * Token[]
         */
        $stack = [];
        foreach ($children as $child) {
            if (OperandProvider::isRegisteredFromSymbol($child) === true) {
                $value2 = array_pop($stack);
                $value1 = array_pop($stack);
                if ($value1 === null || $value2 === null) {
                    throw new ParserErrorException('Could not detect two children for this operator: '.$child);
                }
                $stack[] = new OperandToken(
                    OperandProvider::makeFromSymbol($child),
                    [
                        $value1,
                        $value2,
                    ]
                );

                continue;
            }
            if (\str_contains($child, ':') === true) {
                [$functionName, $argumentsCount] = explode(':', $child);

                if (FunctionProvider::isRegisteredFromSymbol($functionName) === true) {
                    $function = FunctionProvider::makeFromSymbol($functionName);

                    // Add to queue
                    if (intval($argumentsCount) > 0) {
                        $arguments = \array_splice($stack, (-1 * intval($argumentsCount)));
                    } else {
                        $arguments = [];
                    }
                    $stack[] = new FunctionToken($function, $arguments);

                    continue;
                }
                // False alarm, this is not a function
            }
            $stack[] = new SingleToken($child);
        }
        if (count($stack) > 1) {
            throw new ParserErrorException('Stack should contain only one element, some were left off');
        }
        if (empty($stack) === true) {
            $str = implode($children);
            if ($str === '') {
                throw new NoOperationParserException('No operation was done for [empty string]');
            }
            throw new NoOperationParserException('No operation was done for: '.$str);
        }

        return $stack[0];
    }

    /**
     * Gets special tokens
     *
     * @return list<string>
     */
    protected function getSpecialTokens(): array
    {
        $specialTokens = [
            ...OperandProvider::getSymbols(),
            ...FunctionProvider::getSymbols(),
            '(',
        ];
        // Sort operators. Allows to define functions that are compound.
        // Example: "rgb" and "rgb-icc" functions. Sort tokens by length descending prevents
        // selecting a wrong function.
        usort($specialTokens, fn ($a, $b) => (\strlen($b) <=> \strlen($a)));

        return $specialTokens;
    }

    /**
     * Parses a string and returns a list of segments.
     *
     * @return list<string> Tokens found
     */
    protected function doParse(string $token): array
    {
        $specialTokens = [];
        foreach (OperandProvider::get() as $className) {
            $specialTokens[$className::getOperator()] = $className;
        }
        foreach (FunctionProvider::get() as $className) {
            $specialTokens[$className::getFunctionName()] = $className;
        }
        $cursor = new TokenParserCursor($token, $specialTokens);

        $cursor->walk();
        $segments = $cursor->getSegments();

        return (new ShuntingYardAlgorithm(\array_values(array_map(fn ($s) => trim($s), $segments))))->evaluate();
    }
}
