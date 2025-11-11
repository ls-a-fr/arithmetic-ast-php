<?php

declare(strict_types=1);

use Lsa\Arithmetic\Ast\Computing\TokenParser;
use Lsa\Arithmetic\Ast\Functions\Function_;
use Lsa\Arithmetic\Ast\Operands\DivideOperand;
use Lsa\Arithmetic\Ast\Operands\PlusOperand;
use Lsa\Arithmetic\Ast\Providers\FunctionProvider;
use Lsa\Arithmetic\Ast\Providers\OperandProvider;

require_once __DIR__.'/../vendor/autoload.php';

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

FunctionProvider::add(MockFunction::class);
OperandProvider::merge([
    PlusOperand::class,
    DivideOperand::class,
]);

$token = '(mock(1, 2) + 2.6) / 2';
$iterations = 1000;
$time = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    TokenParser::parse($token);
}
echo $iterations.' iterations done in '.round((microtime(true) - $time), 4).' seconds';
