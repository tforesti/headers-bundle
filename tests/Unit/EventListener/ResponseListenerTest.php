<?php

declare(strict_types=1);

namespace Batch\HeadersBundle\Tests\EventListener;

use Batch\HeadersBundle\EventListener\ResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseListenerTest extends TestCase
{
    private static function createResponseListener(array $headers = []): ResponseListener
    {
        return new ResponseListener(new ExpressionLanguage(), $headers);
    }

    private function createResponseEvent(
        ?Request $request = null,
        ?Response $response = null,
        int $requestType = HttpKernelInterface::MASTER_REQUEST
    ): ResponseEvent {
        return new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request ?? new Request(),
            $requestType,
            $response ?? new Response()
        );
    }

    public function testEmptyHeaderListDoesNothing(): void
    {
        $event = $this->createResponseEvent();
        $expectedHeaders = $event->getResponse()->headers->all();
        self::createResponseListener()->onKernelResponse($event);

        static::assertSame($expectedHeaders, $event->getResponse()->headers->all());
    }

    public function testHeadersWithoutConditionAreAlwaysApplied(): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar'],
            ['name' => 'x-foz', 'value' => 'baz'],
        ]);

        $event = $this->createResponseEvent();
        $listener->onKernelResponse($event);

        static::assertSame('bar', $event->getResponse()->headers->get('x-foo'));
        static::assertSame('baz', $event->getResponse()->headers->get('x-foz'));
    }

    public function testHeadersAreAppliedInDeclarationOrder(): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar'],
            ['name' => 'x-foo', 'value' => 'baz'],
        ]);

        $event = $this->createResponseEvent();
        $listener->onKernelResponse($event);

        static::assertSame('baz', $event->getResponse()->headers->get('x-foo'));
    }

    /** @dataProvider conditionProvider */
    public function testHeadersWithConditionAreAppliedSelectively(string $condition, bool $headerShouldBePresent): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar', 'condition' => $condition],
        ]);

        $event = $this->createResponseEvent();
        $listener->onKernelResponse($event);

        static::assertSame($headerShouldBePresent, $event->getResponse()->headers->has('x-foo'));
    }

    public function conditionProvider(): \Generator
    {
        yield 'Invalid condition' => ['false', false];
        yield 'Valid condition' => ['true', true];
    }

    /** @dataProvider strictReturnValueInConditionProvider */
    public function testHeaderConditionMustReturnAvalueStrictlyEqualToTrueToBeValid(
        string $condition,
        bool $headerShouldBePresent
    ): void {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar', 'condition' => $condition],
        ]);

        $event = $this->createResponseEvent();
        $listener->onKernelResponse($event);

        static::assertSame($headerShouldBePresent, $event->getResponse()->headers->has('x-foo'));
    }

    public function strictReturnValueInConditionProvider(): \Generator
    {
        yield 'Numeric string' => ['"1"', false];
        yield 'Integer' => ['1', false];
        yield 'Boolean string' => ['"true"', false];
        yield 'True boolean' => ['true', true];
    }

    public function testRequestIsAvailableInCondition(): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar', 'condition' => 'request.isMethod("POST")'],
        ]);

        $request = Request::create('/', 'POST');
        $event = $this->createResponseEvent($request);
        $listener->onKernelResponse($event);

        static::assertTrue($event->getResponse()->headers->has('x-foo'));
    }

    public function testResponseIsAvailableInCondition(): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar', 'condition' => 'response.getStatusCode() === 201'],
        ]);

        $response = new Response('', 201);
        $event = $this->createResponseEvent(null, $response);
        $listener->onKernelResponse($event);

        static::assertTrue($event->getResponse()->headers->has('x-foo'));
    }

    public function testSubRequestsAreIgnored(): void
    {
        $listener = self::createResponseListener([
            ['name' => 'x-foo', 'value' => 'bar'],
        ]);

        $event = $this->createResponseEvent(null, null, HttpKernelInterface::SUB_REQUEST);
        $listener->onKernelResponse($event);

        static::assertFalse($event->getResponse()->headers->has('x-foo'));
    }
}
