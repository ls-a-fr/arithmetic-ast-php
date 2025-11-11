<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Computing;

use Lsa\Arithmetic\Ast\Exceptions\InvalidOperandTokenException;
use Lsa\Arithmetic\Ast\Functions\Function_;
use Lsa\Arithmetic\Ast\Operands\Operand;
use Lsa\Arithmetic\Ast\Tokens\FunctionToken;

/**
 * Iterates through a string and returns found tokens.
 */
final class TokenParserCursor
{
    /**
     * Base string to parse
     */
    private readonly string $token;

    /**
     * Base string as a character array
     *
     * @var list<string>
     */
    private readonly array $characters;

    /**
     * Every operator and function given at first
     *
     * @var array<string,class-string<Operand|Function_>>
     */
    private readonly array $specialTokens;

    /**
     * Segments found
     *
     * @var array<int, string>
     */
    private array $segments = [];

    /**
     * Current segment row
     */
    private int $row = 0;

    /**
     * Function flag
     */
    private int $inFunction = -1;

    /**
     * Current iterator index
     */
    private int $currentCharacterIndex = 0;

    /**
     * Function parameters.
     *
     * @var list<array<string,string>>
     */
    private array $functionParameters = [];

    /**
     * Creates a new TokenParserCursor
     *
     * @param  string  $token  Base string
     * @param  array<string,class-string<Operand|Function_>>  $specialTokens  Special tokens to take into account
     */
    public function __construct(string $token, array $specialTokens)
    {
        $this->token = $token;
        $this->characters = \str_split($token);
        $this->specialTokens = $specialTokens;
    }

    /**
     * Returns segments after walk
     *
     * @return array<int, string>
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get a class name from a given symbol
     *
     * @param  string  $symbol  Symbol to search for
     * @return class-string<Operand|Function_>
     */
    private function getClassNameFromSymbol(string $symbol): string
    {
        return $this->specialTokens[$symbol];
    }

    /**
     * Get all base symbols plus parentheses and comma
     *
     * @return list<string>
     */
    private function getExtendedSymbols()
    {
        return [
            ...array_keys($this->specialTokens),
            '(',
            ',',
            ')',
        ];
    }

    /**
     * Walk through given string
     */
    public function walk(): void
    {
        $size = count($this->characters);
        while ($this->currentCharacterIndex < $size) {
            // Get character
            $character = $this->characters[$this->currentCharacterIndex];
            if ($this->hasHandledOperator($character) === true) {
                continue;
            }
            // You may wish to put this if condition above to win a lot in performance, but
            // remember that some arguments have spaces in them. Example: url("http://a.fr/path with spaces")
            if ($character === ' ') {
                // Trim while we are at it
                $this->currentCharacterIndex++;

                continue;
            }
            $this->concatToCurrentSegment($character);
        }
    }

    /**
     * Checks if this method had handle this character, and whether it is an operator.
     *
     * @return bool True if character was handled, false otherwise.
     */
    private function hasHandledOperator(string $character): bool
    {
        foreach ($this->getExtendedSymbols() as $operator) {
            // First, this may be a part of a string parameter, same character as an operator. Are we in a function?
            if ($this->inFunction !== -1) {
                if ($character === '(' || $character === ')' || $character === ',') {
                    $this->newSegment($character);

                    return true;
                }
                if ($this->shouldConcatFunctionParameterToCurrentSegment($character) === true) {
                    // Ok, concat previous segment with character and continue
                    $this->concatToCurrentSegment($character);

                    return true;
                }
            }
            // This may be a function, or a string operator (i.e "mod" for modulus)
            if (\strlen($operator) > 1) {
                if ($this->mayPushStringOperator($operator) === true) {
                    return true;
                }

                continue;
            }

            if ($this->mayPushCharacterOperator($character) === true) {
                return true;
            }
            if ($operator === $character) {
                $this->newSegment($character);

                return true;
            }
        }

        return false;
    }

    /**
     * May push a string as an operator, if conditions are met
     *
     * @param  string  $operator  Found operator, if it is one
     */
    protected function mayPushStringOperator(string $operator): bool
    {
        $lookahead = \substr($this->token, $this->currentCharacterIndex, (\strlen($operator) + 1));

        // Check for space and left parenthesis after, to prevent false positives
        if ($lookahead === $operator.' ' || $lookahead === $operator.'(') {
            // This is a function, or a string operator
            $this->updateFunctionFlags(($this->row + 1), $operator);
            $this->newSegment($operator);

            return true;
        }

        return false;
    }

    /**
     * May push a character as an operator, if conditions are met
     *
     * @param  string  $character  Current character
     */
    protected function mayPushCharacterOperator(string $character): bool
    {
        // This is most definitely a single-character operator. But can it be unary, as "+" in "+2" or "-" in "-2"?
        if ($this->isUnaryOperator($character) === true) {
            // Okay, this is an unary operator.
            // Is the next character a number?
            $lookahead = \substr($this->token, $this->currentCharacterIndex, (\strlen($character) + 1));
            if (\is_numeric($lookahead) === true) {
                // This is an unary operator.
                // Do as any classic character.
                $this->concatToCurrentSegment($character);

                return true;
            }
            // Nope, meaning it's a function, or a left parenthesis.
            // Add it to segments
            $this->newSegment($character);

            return true;
        }

        return false;
    }

    /**
     * Update function flags for doParse method
     *
     * @param  int  $row  Targetted row
     * @param  string  $operator  Current operator
     */
    private function updateFunctionFlags(int $row, string $operator): void
    {
        // When "if" is not triggered: it's just an operator, leave it as is
        if ($this->inFunction === -1 && isset($this->specialTokens[$operator]) === true) {
            $this->inFunction = $row;

            $functionClassName = $this->getClassNameFromSymbol($operator);
            /**
             * We could test that, but that would be a waste in performance tests
             *
             * @phpstan-ignore staticMethod.notFound
             */
            $this->functionParameters = $functionClassName::getParameters();
        }
    }

    /**
     * Checks if this character is an unary operator
     *
     * @return bool True if this character is an unary operator, false otherwise
     */
    protected function isUnaryOperator(string $character): bool
    {
        if (isset($this->specialTokens[$character]) === false) {
            return false;
        }
        try {
            /**
             * We could test that, but that would be a waste in performance tests
             *
             * @phpstan-ignore staticMethod.notFound
             */
            return $this->getClassNameFromSymbol($character)::canBeUnary() === true;
        } catch (InvalidOperandTokenException) {
            return false;
        }
    }

    /**
     * Checks if current value should be concatenated, or not
     *
     * @param  string  $character  Current character
     */
    private function shouldConcatFunctionParameterToCurrentSegment(string $character): bool
    {
        // Yes we are. Do this function allows the current character?
        $segmentsSinceFunctionToken = array_slice($this->segments, ($this->inFunction + 1));
        $currentParameterIndex = $this->getParameterIndex($segmentsSinceFunctionToken);

        if (empty($segmentsSinceFunctionToken) === false) {
            $segmentTokenIndex = (count($segmentsSinceFunctionToken) - 1);
            $tentativeSegment = $segmentsSinceFunctionToken[$segmentTokenIndex].$character;
        } else {
            $segmentTokenIndex = (count($this->segments) - 1);
            $tentativeSegment = $this->segments[$segmentTokenIndex].$character;
        }

        // Ignore if current parameter index is undefined: this may be an invalid call
        if (isset($this->functionParameters[$currentParameterIndex]) === true) {
            // Get current parameter
            $currentParameter = $this->functionParameters[$currentParameterIndex];
            $currentParameterType = \array_values($currentParameter)[0];
            // If current parameter allows for this character...
            if (FunctionToken::allowCharacter($currentParameterType, $tentativeSegment) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get parameter index from functions segments
     *
     * @param  list<string>  $segmentsSinceFunctionToken
     */
    private function getParameterIndex(array $segmentsSinceFunctionToken): int
    {
        $currentParameterIndex = 0;
        $leftParenthesisCount = 0;

        foreach ($segmentsSinceFunctionToken as $functionSegmentIndex => $parameterToken) {
            if ($parameterToken === '(') {
                $leftParenthesisCount++;

                continue;
            }
            if ($parameterToken === ')') {
                $leftParenthesisCount--;

                continue;
            }
            if ($parameterToken === ',') {
                $currentParameterIndex++;

                continue;
            }

            // Ignore inner expressions
            if ($leftParenthesisCount > 1) {
                continue;
            }

            // Ignore if current parameter index is undefined: this may be an invalid call
            if (isset($this->functionParameters[$currentParameterIndex]) === false) {
                break;
            }

            $parameter = $this->functionParameters[$currentParameterIndex];
            $parameterMode = (\array_keys($parameter)[0]);
            $parameterType = (\array_values($parameter)[0]);

            if ($functionSegmentIndex === (count($segmentsSinceFunctionToken) - 1)) {
                // Last, stop
                break;
            }

            $tentativeCharacter = $segmentsSinceFunctionToken[$functionSegmentIndex];
            if (FunctionToken::allowCharacter($parameterType, $tentativeCharacter) === true) {
                // Found parameter, continue
                continue;
            }
            if ($parameterMode === Function_::MODE_OPTIONAL) {
                // Parameter was not filled, increase current parameter index
                $currentParameterIndex++;
            }
        }

        return $currentParameterIndex;
    }

    /**
     * Push a new segment
     *
     * @param  string  $char  Character to push
     */
    private function newSegment(string $char): void
    {
        if (isset($this->segments[$this->row]) === true && $this->segments[$this->row] !== '') {
            $this->row++;
        }
        $this->segments[$this->row] = $char;
        $this->row++;
        $this->currentCharacterIndex += \strlen($char);
    }

    /**
     * Concats character to current segment
     *
     * @param  string  $char  Character to push
     */
    private function concatToCurrentSegment(string $char): void
    {
        if (isset($this->segments[$this->row]) === false) {
            $this->segments[$this->row] = '';
        }
        $this->segments[$this->row] .= $char;
        $this->currentCharacterIndex += \strlen($char);
    }
}
