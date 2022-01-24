<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Zing\CodingStandard\Set\ECSSetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(ECSSetList::PHP_73);
    $containerConfigurator->import(ECSSetList::CUSTOM);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(
        Option::SKIP,
        [
            \PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff::class => ['*/migrations/*'],
            // Will be removed in a future major version.
            \SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,
        ]
    );
    $parameters->set(
        Option::PATHS,
        [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php', __DIR__ . '/rector.php']
    );
};
