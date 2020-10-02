<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Batch\HeadersBundle\EventListener\ResponseListener;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

return static function (ContainerConfigurator $configurator): void {
    $configurator->services()
        ->set('batch_headers.expression_language', ExpressionLanguage::class)
            ->args([(new ReferenceConfigurator(CacheItemPoolInterface::class))->ignoreOnInvalid()])
        ->set('batch_headers.response_listener', ResponseListener::class)
            ->args([
                new ReferenceConfigurator('batch_headers.expression_language'),
                'Abstract argument: Configured headers',
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.response', 'priority' => -32])
    ;
};
