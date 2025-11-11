<?php

declare(strict_types=1);

namespace Lsa\Arithmetic\Ast\Providers;

/**
 * Base provider class, extended by FunctionProvider and OperandProvider.
 *
 * @template T of object
 */
abstract class Provider
{
    /**
     * Elements in this provider
     *
     * @var array<class-string<Provider<T>>,list<class-string<T>>>
     */
    private static $elements = [];

    /**
     * Get a list of elements from this provider
     *
     * @return list<class-string<T>>
     */
    public static function get()
    {
        // phpcs:disable Squiz.Formatting.OperatorBracket.MissingBrackets
        return self::$elements[static::class] ?? [];
    }

    /**
     * Adds an element to this provider.
     *
     * @param  class-string<T>  $className
     */
    public static function add(string $className): void
    {
        if (isset(self::$elements[static::class]) === false) {
            self::$elements[static::class] = [];
        }
        self::$elements[static::class][] = $className;
    }

    /**
     * Merge functions in main provider
     *
     * @param  list<class-string<T>>  $classNames
     */
    public static function merge(array $classNames): void
    {
        self::$elements[static::class] = \array_merge((self::$elements[static::class] ?? []), $classNames);
    }

    /**
     * Removes an element from this provider
     *
     * @param  class-string<T>  $className
     */
    public static function remove(string $className): void
    {
        self::$elements[static::class] = \array_values(
            \array_filter((self::$elements[static::class] ?? []), fn ($c) => $c !== $className)
        );
    }

    /**
     * Checks if the given symbol is registered.
     *
     * @return bool True if the symbol is registered, false otherwise.
     */
    abstract public static function isRegisteredFromSymbol(string $symbol): bool;

    /**
     * Make an object from the given symbol.
     *
     * @param  string  $symbol  The symbol
     * @return T
     */
    abstract public static function makeFromSymbol(string $symbol): object;

    /**
     * Make an object from the given symbol.
     *
     * @param  string  $symbol  The symbol
     * @return class-string<T>
     */
    abstract public static function getClassFromSymbol(string $symbol): string;

    /**
     * Get all registered symbols.
     *
     * @return list<string>
     */
    abstract public static function getSymbols(): array;
}
