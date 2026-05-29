<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Bridge\Symfony\Routing\SymfonyRoutesProvider;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true
    )
    ->withComposerBased(doctrine: true, symfony: true)
    ->withSymfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withSymfonyContainerPhp(__DIR__ . '/tests/symfony-container.php')
    ->registerService(SymfonyRoutesProvider::class, SymfonyRoutesProviderInterface::class);
