<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return RectorConfig::configure()
    ->withAttributesSets()
    ->withPreparedSets(
        typeDeclarations: true,
        symfonyCodeQuality: true,
    )
    ->withRules([
        ReadOnlyPropertyRector::class
    ]);
