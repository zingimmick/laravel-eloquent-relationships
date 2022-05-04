<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Zing\CodingStandard\Set\ECSSetList;
use \Symplify\EasyCodingStandard\Config\ECSConfig ;
return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([ECSSetList::PHP_73, ECSSetList::CUSTOM]);
    $parameters = $ecsConfig->parameters();
    $ecsConfig->parallel();
    $ecsConfig->skip([
        \PHP_CodeSniffer\Standards\PSR1\Sniffs\Classes\ClassDeclarationSniff::class => ['*/migrations/*'],
        \PhpCsFixer\Fixer\ReturnNotation\SimplifiedNullReturnFixer::class => [
            __DIR__ . '/src/Relations/BelongsToOne.php',
            __DIR__ . '/src/Relations/MorphToOne.php',
        ],
        // Will be removed in a future major version.
        \SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,
    ]);
    $parameters->set(
        Option::PATHS,
        [__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php', __DIR__ . '/rector.php']
    );
};
