<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Zing\CodingStandard\Set\ECSSetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([ECSSetList::PHP_73, ECSSetList::CUSTOM]);
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
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php', __DIR__ . '/rector.php']);
};
