<?php

declare(strict_types=1);

namespace Batch\HeadersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('batch_headers');

        // @phpstan-ignore-next-line
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('headers')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifTrue(function ($v): bool {
                                return \is_string($v) && \strpos($v, ':') !== false;
                            })
                            ->then(function (string $v): array {
                                [$name, $value] = \explode(':', $v, 2);
                                return ['name' => \trim($name), 'value' => \trim($value)];
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function ($v): bool {
                                return \is_array($v)
                                    && \count($v) === 1
                                    && \is_string(\key($v))
                                    && \is_string(\reset($v));
                            })
                            ->then(function (array $v): array {
                                return ['name' => \key($v), 'value' => \reset($v)];
                            })
                        ->end()
                        ->normalizeKeys(false)
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('value')->end()
                            ->scalarNode('condition')
                                ->defaultNull()
                            ->end()
                            ->booleanNode('replace')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
