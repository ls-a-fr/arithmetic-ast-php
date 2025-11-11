<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Functions;

/**
 * An example function.
 */
class RandFunction extends Function_
{
    public static function getFunctionName(): string
    {
        return 'rand';
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
        $arg0 = \intval($args[0]);
        $arg1 = \intval($args[1]);
        if (empty($args) === true) {
            // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
            return rand(0, 100000) / 100000;
        }
        if (count($args) === 1) {
            // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
            return rand(0, $arg0 * 100000) / 100000;
        }

        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return rand($arg0, $arg1);
    }
}
