<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Tests\Providers;

use Lsa\Arithmetic\Ast\Exceptions\InvalidOperationException;
use Lsa\Arithmetic\Ast\Exceptions\NoOperationParserException;

final class TokenParserDataProvider
{
    public static function validDataProvider(): array
    {
        return [
            [
                ['1 + 2 * 3'],
                7.0,
                7.0,
            ],
            [
                ['1 + 2'],
                3.0,
                3.0,
            ],
            [
                ['1 * 2'],
                2.0,
                2.0,
            ],
            [
                ['1 / 2'],
                0.5,
                0.5,
            ],
            [
                ['1 % 2'],
                1.0,
                1.0,
            ],
            [
                ['4 % 2'],
                0.0,
                0.0,
            ],
            [
                ['10 % 3'],
                1.0,
                1.0,
            ],
            [
                ['1 + (2 * 3 % 5 - (2 + 3 + 4) + (5 / 2)) + (3 * 4)'],
                7.5,
                7.5,
            ],
            [
                ['1 + mock()'],
                1.42,
                1.42,
            ],
            [
                ['1 + mock(1)'],
                2.42,
                2.42,
            ],
            [
                ['(mock(1, 2) + 2.6) / 2'],
                2.26,
                2.26,
            ],
            [
                ['mock-with-dashes(mock-with-dashes(mock-with-dashes(mock-with-dashes(2))))'],
                32.0,
                32.0,
            ],
        ];
    }

    public static function invalidDataProvider(): array
    {
        return [
            [
                ['1pt + 2'],
                InvalidOperationException::class,
                'Unit power must be same for + operand',
            ],
            [
                ['1 + 2pt'],
                InvalidOperationException::class,
                'Unit power must be same for + operand',
            ],
            [
                ['1 + (2 + 3in)'],
                InvalidOperationException::class,
                'Unit power must be same for + operand',
            ],
            [
                ['1 - (2in + 3in)'],
                InvalidOperationException::class,
                'Unit power must be same for - operand',
            ],
            [
                [''],               // empty string
                NoOperationParserException::class,
                'No operation was done for [empty string]',
            ],
        ];
    }
}
