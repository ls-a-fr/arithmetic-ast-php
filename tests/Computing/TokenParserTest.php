<?php

declare(strict_types=1);
require_once __DIR__.'/../../vendor/autoload.php';

use Lsa\Arithmetic\Ast\Computing\TokenParser;
use Lsa\Arithmetic\Ast\Functions\Function_;
use Lsa\Arithmetic\Ast\Operands\DivideOperand;
use Lsa\Arithmetic\Ast\Operands\MinusOperand;
use Lsa\Arithmetic\Ast\Operands\ModulusOperand;
use Lsa\Arithmetic\Ast\Operands\MultiplyOperand;
use Lsa\Arithmetic\Ast\Operands\PlusOperand;
use Lsa\Arithmetic\Ast\Providers\FunctionProvider;
use Lsa\Arithmetic\Ast\Providers\OperandProvider;
use Lsa\Arithmetic\Ast\Tests\Providers\TokenParserDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class TokenParserTest extends TestCase
{
    #[DataProviderExternal(TokenParserDataProvider::class, 'validDataProvider')]
    public function test_validation_with_valid_values(array|string $value, mixed $expected, float $normalized): void
    {
        FunctionProvider::add(MockFunction::class);
        FunctionProvider::add(MockWithDashesFunction::class);
        OperandProvider::merge([
            PlusOperand::class,
            MinusOperand::class,
            MultiplyOperand::class,
            DivideOperand::class,
            ModulusOperand::class,
        ]);
        $token = TokenParser::parse(...$value);
        $this->assertSame($expected, $token->evaluate());
        $this->assertSame($normalized, $token->getNormalizedValue());
    }

    #[DataProviderExternal(TokenParserDataProvider::class, 'invalidDataProvider')]
    public function test_validation_with_invalid_values(mixed $value, string $exceptionClass, string $message): void
    {
        FunctionProvider::add(MockFunction::class);
        FunctionProvider::add(MockWithDashesFunction::class);
        OperandProvider::merge([
            PlusOperand::class,
            MinusOperand::class,
            MultiplyOperand::class,
            DivideOperand::class,
            ModulusOperand::class,
        ]);
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);
        TokenParser::parse(...$value)->evaluate();
    }
}

class MockFunction extends Function_
{
    public static function getFunctionName(): string
    {
        return 'mock';
    }

    public static function getParameters(): array
    {
        return [
            [self::MODE_OPTIONAL => self::TYPE_NUMERIC],
            [self::MODE_OPTIONAL => self::TYPE_NUMERIC],
        ];
    }

    public function evaluate(...$args): string|float
    {
        if (count($args) === 0) {
            return 0.42;
        }

        if (count($args) === 1) {
            return floatval($args[0]) + 0.42;
        }

        $average = (floatval($args[0]) + floatval($args[1])) / 2;

        return $average + 0.42;
    }
}

class MockWithDashesFunction extends Function_
{
    public static function getFunctionName(): string
    {
        return 'mock-with-dashes';
    }

    public static function getParameters(): array
    {
        return [
            [self::MODE_REQUIRED => self::TYPE_NUMERIC],
        ];
    }

    public function evaluate(...$args): string|float
    {
        return floatval($args[0]) * 2;

    }
}
