<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Computing;

use Lsa\Arithmetic\Ast\Exceptions\ShuntingYardException;
use Lsa\Arithmetic\Ast\Operands\Operand;
use Lsa\Arithmetic\Ast\Providers\FunctionProvider;
use Lsa\Arithmetic\Ast\Providers\OperandProvider;

/**
 * Implementation of Shunting Yard Algorithm
 * This implementation adds a new syntax: `function:nb`, where `nb` represents the parameters count.
 * This is mandatory because some functions are variadic.
 *
 * @link https://en.wikipedia.org/wiki/Shunting_yard_algorithm
 */
class ShuntingYardAlgorithm
{
    /**
     * Segments provided after parse
     *
     * @var list<string>
     */
    public readonly array $segments;

    /**
     * Operator stack
     *
     * @var list<string>
     */
    private array $operatorStack = [];

    /**
     * Output queue
     *
     * @var list<string>
     */
    private array $outputQueue = [];

    /**
     * Current arguments counter. Will keep track of inner function calls
     *
     * @var list<int>
     */
    private array $currentArgsCounter = [];

    /**
     * Current function remaining parameters. Will keep track of inner function calls
     *
     * @var list<array<string,string>>
     */
    private array $currentFunctionRemainingParameters = [];

    /**
     * Creates a new ShuntingYardAlgorithm.
     *
     * @param  list<string>  $segments  Segments to resolve
     */
    public function __construct(array $segments)
    {
        $this->segments = $segments;
    }

    /**
     * Evaluates segments
     *
     * @return list<string> Segments found
     */
    public function evaluate(): array
    {
        foreach ($this->segments as $segment) {
            // - A function:
            if ($this->isFunction($segment) === true) {
                // Push it onto the operator stack
                $this->pushOperator($segment);
                $this->notifyFunction();

                continue;
            }
            // An operator o1:
            if ($this->isOperator($segment) === true) {
                // While :
                // - there is an operator o2 at the top of the operator stack which is not a left parenthesis,
                // - and (o2 has greater precedence than o1 or (o1 and o2 have the same precedence and o1 is left-associative))
                // ):
                $o1Precedence = $this->getOperatorPrecedence($segment);
                $this->rewindOperatorStackSafe(fn ($op) => $op !== '(', function ($op) use ($o1Precedence, $segment) {
                    $o2Precedence = $this->getOperatorPrecedence($op);
                    // phpcs:disable Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceAfterOpen
                    // phpcs:disable PEAR.ControlStructures.MultiLineCondition.Alignment
                    // phpcs:disable Generic.WhiteSpace.ArbitraryParenthesesSpacing.SpaceBeforeClose
                    if (
                        (
                            $o2Precedence === $o1Precedence
                            && $this->getOperatorAssociativity($segment) === Operand::LEFT_ASSOCIATIVE
                        )
                        || $o2Precedence > $o1Precedence
                    ) {
                        // Pop o2 from the operator stack into the output queue
                        $this->moveLastOperatorToOutputQueue();
                    }
                });
                // Push o1 onto the operator stack
                $this->pushOperator($segment);

                continue;
            }
            // - A ",":
            if ($this->isComma($segment) === true) {
                // While the operator at the top of the operator stack is not a left parenthesis:
                $this->rewindOperatorStack(fn ($op) => $op !== '(', function () {
                    $this->increaseNumberOfArguments();
                    // Pop the operator from the operator stack into the output queue
                    $this->moveLastOperatorToOutputQueue();
                });

                continue;
            }
            // - A left parenthesis (i.e. "("):
            if ($this->isLeftParenthesis($segment) === true) {
                // Push it onto the operator stack
                $this->pushOperator($segment);

                continue;
            }
            if ($this->isRightParenthesis($segment) === true) {
                // While the operator at the top of the operator stack is not a left parenthesis:
                $this->rewindOperatorStack(fn ($op) => $op !== '(', function () {
                    $this->moveLastOperatorToOutputQueue();
                }, 'Cannot find previous left parenthesis');
                // Assert the operator stack is not empty
                // Note: This is automatic with rewindOperatorStack
                // Pop the left parenthesis from the operator stack and discard it
                $this->discardLastOperator();
                if ($this->isLastOperatorFunction() === true) {
                    array_pop($this->currentFunctionRemainingParameters);
                    $this->moveLastOperatorToOutputQueue();
                }

                continue;
            }
            $this->pushValue($segment);
            array_pop($this->currentFunctionRemainingParameters);
        }
        // Assert the operator on top of the stack is not a (left) parenthesis
        // Pop the function from the operator stack into the output queue
        $this->pushOperatorStackToOutputQueue();

        return $this->outputQueue;
    }

    /**
     * Checks if the current segment is a function
     *
     * @return bool True if the current segment is a function, false otherwise
     */
    public function isFunction(?string $segment): bool
    {
        if ($segment === null) {
            return false;
        }

        return \array_search($segment, FunctionProvider::getSymbols()) !== false;
    }

    /**
     * Checks if the current segment is an operator
     *
     * @return bool True if the current segment is an operator, false otherwise
     */
    public function isOperator(?string $segment): bool
    {
        if ($segment === null) {
            return false;
        }

        return \array_search($segment, OperandProvider::getSymbols()) !== false;
    }

    /**
     * Checks if the current segment is a comma
     *
     * @return bool True if the current segment is a comma, false otherwise
     */
    public function isComma(?string $segment): bool
    {
        if ($segment === null) {
            return false;
        }

        return $segment === ',';
    }

    /**
     * Checks if the current segment is a left parenthesis
     *
     * @return bool True if the current segment is a left parenthesis, false otherwise
     */
    public function isLeftParenthesis(?string $segment): bool
    {
        if ($segment === null) {
            return false;
        }

        return $segment === '(';
    }

    /**
     * Checks if the current segment is a right parenthesis
     *
     * @return bool True if the current segment is a right parenthesis, false otherwise
     */
    public function isRightParenthesis(?string $segment): bool
    {
        if ($segment === null) {
            return false;
        }

        return $segment === ')';
    }

    /**
     * Gets the current operator associativity
     *
     * @return Operand::LEFT_ASSOCIATIVE|Operand::RIGHT_ASSOCIATIVE
     */
    protected function getOperatorAssociativity(string $segment): string
    {
        return OperandProvider::getClassFromSymbol($segment)::getAssociativity();
    }

    /**
     * Gets operator precedence
     */
    protected function getOperatorPrecedence(string $segment): int
    {
        return OperandProvider::getClassFromSymbol($segment)::getPrecedence();
    }

    /**
     * Checks if last operator was a left parenthesis
     *
     * @return bool True if last operator was a left parenthesis, false otherwise
     */
    protected function isLastOperatorLeftParenthesis(): bool
    {
        return $this->operatorStack[(count($this->operatorStack) - 1)] === '(';
    }

    /**
     * Returns last function, if there is one
     */
    protected function getLastFunction(): ?string
    {
        if (empty($this->operatorStack) === true || $this->isLastOperatorFunction() === false) {
            return null;
        }

        return FunctionProvider::getClassFromSymbol($this->operatorStack[(count($this->operatorStack) - 1)]);
    }

    /**
     * Checks if last operator was a function
     *
     * @return bool True if last operator was a function, false otherwise
     */
    protected function isLastOperatorFunction(): bool
    {
        if (empty($this->operatorStack) === true) {
            return false;
        }

        $operator = ($this->operatorStack[(count($this->operatorStack) - 1)] ?? null);

        return $this->isFunction($operator);
    }

    /**
     * Increase number of arguments for current function
     *
     * @throws ShuntingYardException
     */
    protected function increaseNumberOfArguments(): void
    {
        $lastIndex = (count($this->currentArgsCounter) - 1);
        if (isset($this->currentArgsCounter[$lastIndex]) === false) {
            throw new ShuntingYardException('Cannot increase number of arguments, index not found');
        }
        $this->currentArgsCounter[$lastIndex]++;
    }

    /**
     * Gets last operator if any, without moving the stack
     */
    protected function lookLastOperator(): ?string
    {
        if (empty($this->operatorStack) === true) {
            return null;
        }

        return $this->operatorStack[(count($this->operatorStack) - 1)];
    }

    /**
     * Discards last operator
     */
    protected function discardLastOperator(): void
    {
        array_pop($this->operatorStack);
    }

    /**
     * Notifies algorithm is in a function state
     *
     * @throws ShuntingYardException
     */
    protected function notifyFunction(): void
    {
        if (empty($this->currentArgsCounter) === false) {
            $this->increaseNumberOfArguments();
        }
        $functionClass = $this->getLastFunction();
        if ($functionClass === null) {
            throw new ShuntingYardException('Could not fetch last function');
        }
        $this->currentFunctionRemainingParameters = $functionClass::getParameters();
        $this->currentArgsCounter[] = 0;
    }

    /**
     * Push operator stack to output queue
     *
     * @throws ShuntingYardException
     */
    protected function pushOperatorStackToOutputQueue(): void
    {
        while (empty($this->operatorStack) === false) {
            $op = $this->lookLastOperator();
            if ($this->isLeftParenthesis($op) === true) {
                throw new ShuntingYardException('Invalid left parenthesis found in expression');
            }
            $this->moveLastOperatorToOutputQueue();
        }
    }

    /**
     * Rewind the entire stack while condition is fulfilled
     *
     * @param  callable(string):bool  $condition  Condition to fulfill
     * @param  callable(string):void  $loop  Loop to execute
     * @param  ?string  $message  Message to throw if condition stays true until the end of operation stack
     *
     * @throws ShuntingYardException
     */
    protected function rewindOperatorStack(callable $condition, callable $loop, ?string $message = null): void
    {
        if ($message === null) {
            $message = 'Operator stack is empty';
        }
        $this->rewindOperatorStackSafe($condition, $loop);
        if (empty($this->operatorStack) === true) {
            throw new ShuntingYardException($message);
        }
    }

    /**
     * Rewind the entire stack while condition is fulfilled, safely. Meaning no exception thrown
     *
     * @param  callable(string):bool  $condition  Condition to fulfill
     * @param  callable(string):void  $loop  Loop to execute
     */
    protected function rewindOperatorStackSafe(callable $condition, callable $loop): void
    {
        $it = (count($this->operatorStack) - 1);
        while (empty($this->operatorStack) === false && $it >= 0) {
            $operator = $this->lookLastOperator();
            if ($operator === null || $condition($operator) === false) {
                break;
            }
            $loop($operator);
            $it--;
        }
    }

    /**
     * Pushes an operator to the operator stack
     *
     * @param  string  $segment  Operator segment
     */
    protected function pushOperator(string $segment): void
    {
        $this->operatorStack[] = $segment;
    }

    /**
     * Moves last operator to output queue
     */
    protected function moveLastOperatorToOutputQueue(): void
    {
        $operator = $this->lookLastOperator();
        if ($this->isFunction($operator) === true) {
            // Pop the function from the operator stack into the output queue
            $lastFunction = array_pop($this->operatorStack);
            $argsCounter = array_pop($this->currentArgsCounter);
            $this->pushToOutputQueue($lastFunction.':'.strval($argsCounter));
        } else {
            $lastOperator = array_pop($this->operatorStack);
            $this->pushToOutputQueue($lastOperator);
        }
    }

    /**
     * Pushes a segment to output queue
     *
     * @param  ?string  $segment  Segment to push. If null, no-op.
     */
    protected function pushToOutputQueue(?string $segment): void
    {
        if ($segment === null) {
            return;
        }
        $this->outputQueue[] = $segment;
    }

    /**
     * Pushes a value to output queue
     *
     * @param  string  $segment  Value to push
     */
    protected function pushValue(string $segment): void
    {
        if (
            empty($this->currentArgsCounter) === false
            && $this->isLastOperatorLeftParenthesis() === true
        ) {
            $this->increaseNumberOfArguments();
        }
        $this->pushToOutputQueue($segment);
    }
}
