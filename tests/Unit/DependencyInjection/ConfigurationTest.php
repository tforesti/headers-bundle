<?php

declare(strict_types=1);

namespace Batch\HeadersBundle\Tests\DependencyInjection;

use Batch\HeadersBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        static::assertSame(['headers' => []], $config);
    }

    /** @dataProvider validHeaderFormatProvider */
    public function testValidHeaderFormat(array $headers, array $expectedConfig): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['headers' => $headers]]);

        static::assertSame($expectedConfig, $config);
    }

    public function validHeaderFormatProvider(): \Generator
    {
        yield 'Full' => [
            [['name' => 'x-foo', 'value' => 'bar', 'condition' => 'true', 'replace' => true]],
            ['headers' => [['name' => 'x-foo', 'value' => 'bar', 'condition' => 'true', 'replace' => true]]],
        ];

        yield 'Nullable condition' => [
            [['name' => 'x-foo', 'value' => 'bar', 'condition' => null]],
            ['headers' => [['name' => 'x-foo', 'value' => 'bar', 'condition' => null, 'replace' => true]]],
        ];

        yield 'Unique key-value array' => [
            [['x-foo' => 'bar']],
            ['headers' => [['name' => 'x-foo', 'value' => 'bar', 'condition' => null, 'replace' => true]]],
        ];

        yield 'Semicolon separated string' => [
            ['x-foo: bar'],
            ['headers' => [['name' => 'x-foo', 'value' => 'bar', 'condition' => null, 'replace' => true]]],
        ];

        yield 'Multi-semicolon separated string' => [
            ['x-foo: bar:baz'],
            ['headers' => [['name' => 'x-foo', 'value' => 'bar:baz', 'condition' => null, 'replace' => true]]],
        ];
    }

    /** @dataProvider invalidHeaderFormatProvider */
    public function testInvalidHeaderFormat(array $headers): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $processor = new Processor();
        $processor->processConfiguration(new Configuration(), [['headers' => $headers]]);
    }

    public function invalidHeaderFormatProvider(): \Generator
    {
        yield 'Multi key-value array' => [
            [['x-foo' => 'bar', 'x-foz' => 'baz']],
        ];

        yield 'String without semicolon' => [
            ['x-foo bar'],
        ];
    }
}
