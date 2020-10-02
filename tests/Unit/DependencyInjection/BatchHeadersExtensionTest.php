<?php

declare(strict_types=1);

namespace Batch\HeadersBundle\Tests\DependencyInjection;

use Batch\HeadersBundle\DependencyInjection\BatchHeadersExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BatchHeadersExtensionTest extends TestCase
{
    /** @var ContainerBuilder */
    private $container;

    /** @var BatchHeadersExtension */
    private $extension;

    public function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new BatchHeadersExtension();
    }

    public function testContainerWithoutConfig(): void
    {
        $this->extension->load([], $this->container);
        $listenerDefinition = $this->container->getDefinition('batch_headers.response_listener');

        $firstArg = $listenerDefinition->getArgument(0);
        self::assertInstanceOf(Reference::class, $firstArg);
        self::assertSame('batch_headers.expression_language', (string) $firstArg);

        self::assertSame([], $listenerDefinition->getArgument(1));
    }

    public function testContainerWithConfig(): void
    {
        $headers = [
            ['name' => 'x-foo', 'value' => 'bar', 'condition' => null],
        ];

        $this->extension->load([['headers' => $headers]], $this->container);
        $listenerDefinition = $this->container->getDefinition('batch_headers.response_listener');

        $firstArg = $listenerDefinition->getArgument(0);
        self::assertInstanceOf(Reference::class, $firstArg);
        self::assertSame('batch_headers.expression_language', (string) $firstArg);

        self::assertSame($headers, $listenerDefinition->getArgument(1));
    }
}
