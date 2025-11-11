# Arithmetic AST

This documentation is also available in these languages:
- [Français](docs/LISEZMOI.md)

This library provides a implementation of Abstract Syntax Tree for arithmetic operations.  
For more information, see these links:
- [https://en.wikipedia.org/wiki/Abstract_syntax_tree](https://en.wikipedia.org/wiki/Abstract_syntax_tree)
- [https://en.wikipedia.org/wiki/Reverse_Polish_notation](https://en.wikipedia.org/wiki/Reverse_Polish_notation)
- [https://en.wikipedia.org/wiki/Shunting_yard_algorithm](https://en.wikipedia.org/wiki/Shunting_yard_algorithm)

Code sample:
```php
<?php

$tokens = [
    TokenParser::parse('1 + 2 * 3'),
    TokenParser::parse('1 + (2 * 3 % 5 - (2 + 3 + 4) + (5 / 2)) + (3 * 4)'),
    TokenParser::parse('(mock(1, 2) + 2.6) / 2'),
    TokenParser::parse('mock-with-dashes(mock-with-dashes(mock-with-dashes(2)))')
];
```

## Features

This library includes :
- Support for following operators : `+`, `-`, `*`, `/`, `%`
- Allowance to create new operators, with custom symbols, whether single character or string
- Unary operator managament
- Functions support
- Function string parameters
- Variadic function parameters
- Support for following units: `mm`, `cm`, `pt`, `pc`, `in`, `%` and related converters, plus support to create your own

For every operation, you can use `evaluate` method to get its result.

## Why?

First, because why not? We could not find an arithmetic abstract syntax tree on composer and felt it was missing.  
Next, for a little background, this package is used in [XSL-Core package](https://github.com/ls-a-fr/xsl-core-php), to handle various XML function calls in attributes.

## Installation

This library is available on Composer. Install it with:
```sh
composer require ls-a/arithmetic-ast
```

## Changelog

Please refer to the [CHANGELOG](CHANGELOG.md) file to see the latest changes.

## Support

We put our heart into delivering high-quality products that are accessible to everyone. If you like our work, don’t hesitate to reach out to us for your next project!

## Contributing

Contributions are governed by the [CONTRIBUTING](https://github.com/ls-a-fr/.github/CONTRIBUTING.md) file.

## Security

If you’ve found a bug or vulnerability, please contact us by email at [contact@ls-a.fr](mailto:contact@ls-a.fr) instead of opening an issue, in order to protect the security of other users.

## Credits

- Renaud Berthier

## License

The MIT License (MIT). Please see License File for more information.