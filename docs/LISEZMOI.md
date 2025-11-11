# Arithmetic AST

Ce package fournit une implémentation d'un arbre syntaxique abstrait (Abstract Syntax Tree) pour des opérations arithmétiques.  
Pour davantage d'informations, rendez-vous sur ces liens :
- [https://en.wikipedia.org/wiki/Abstract_syntax_tree](https://en.wikipedia.org/wiki/Abstract_syntax_tree)
- [https://en.wikipedia.org/wiki/Reverse_Polish_notation](https://en.wikipedia.org/wiki/Reverse_Polish_notation)
- [https://en.wikipedia.org/wiki/Shunting_yard_algorithm](https://en.wikipedia.org/wiki/Shunting_yard_algorithm)

Exemple de code :
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

Cette bibliothèque fournit :
- Un support pour les opérateurs suivants : `+`, `-`, `*`, `/`, `%`
- Une possibilité de créer de nouveaux opérateurs, avec les symboles de votre choix, qu'il s'agisse d'uniques caractères ou de chaînes de caractères
- Un support des opérateurs unaires
- Un support des fonctions
- Des fonctions supportant des paramètres au format texte
- Des fonctions avec des paramètres optionnels
- Un support pour les unités suivantes : `mm`, `cm`, `pt`, `pc`, `in`, `%` et leurs convertisseurs associés, comme la possibilité d'en créer de nouvelles

Pour chaque opération, vous pourrez utiliser la méthode `evaluate` pour obtenir le résultat.

## Pourquoi ?

Premièrement, pourquoi pas ? Nous n'avons pas trouvé ce type de bibliothèque sur composer et avons trouvé que cela manquait.  
Ensuite, pour l'histoire, ce package est utilisé dans la [bibliothèque XSL-Core](https://github.com/ls-a-fr/xsl-core-php), pour gérer les différents appels de fonctions dans des attributs XML.

## Installation

Ce package sera (bientôt) disponible sur Composer. Pour l'installer :
```sh
composer require ls-a/arithmetic-ast
```

## Journal des modifications

Veuillez consulter le fichier [CHANGELOG](CHANGELOG.md) pour voir les dernières modifications.

## Support

Nous mettons du coeur à l'ouvrage pour proposer des produits de qualité et accessibles à toutes et tous. Si vous aimez notre travail, n'hésitez pas à faire appel à nous pour votre prochain projet !  

## Contributions

Les contributions sont régies par le fichier [CONTRIBUTING](https://github.com/ls-a-fr/.github/CONTRIBUTING.md).

## Sécurité

Si vous avez déniché un bug ou une faille, merci de nous contacter par mail à [mailto:contact@ls-a.fr](contact@ls-a.fr) en lieu et place d'une issue, pour respecter la sécurité des autres usagers.


## Crédits

- Renaud Berthier

## Licence

Code déposé sous licence MIT. Rendez-vous sur le fichier LICENSE pour davantage d'informations.