<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Set\ValueObject\SetList;

return function (RectorConfig $rectorConfig): void {
    $builder = new RectorConfigBuilder();

    $builder->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
    ]);

    $builder->withImportNames(importShortClasses: false, removeUnusedImports: true);
    $builder->withComposerBased(symfony: true);

    $builder->withSets([
        SetList::TYPE_DECLARATION,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
    ]);

    $builder($rectorConfig);
};
