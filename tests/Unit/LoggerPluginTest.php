<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Tests\Unit;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Promise\FulfilledPromise;
use Http\Promise\RejectedPromise;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPickerInterface;
use Solitus0\HttplugLoggerBundle\LoggerPlugin;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection;

final class LoggerPluginTest extends TestCase
{
    public function testHandleRequestLogsBasedOnLevelPicker(): void
    {
        $request = new Request('GET', 'https://example.com');
        $response = new Response(400);

        $collection = $this->createMock(PsrHttpMessageParserCollection::class);
        $collection
            ->expects(self::once())
            ->method('parse')
            ->with($request, $response, null)
            ->willReturn(['httpRequest' => ['status' => 400]])
        ;

        $levelPicker = $this->createMock(LogLevelPickerInterface::class);
        $levelPicker
            ->expects(self::once())
            ->method('pick')
            ->with(self::callback(static function (array $data): bool {
                return isset($data['client_name']) && $data['client_name'] === 'client-a';
            }))
            ->willReturn('warning')
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with('Bad request', self::callback(static function (array $context): bool {
                return $context['httpRequest']['status'] === 400 && $context['client_name'] === 'client-a';
            }))
        ;

        $plugin = new LoggerPlugin($collection, $levelPicker, $logger, 'client-a');

        $promise = $plugin->handleRequest($request, static fn (): FulfilledPromise => new FulfilledPromise($response), static fn () => null);
        $result = $promise->wait();

        self::assertSame($response, $result);
    }

    public function testHandleRequestLogsAndRethrowsOnRejectedPromise(): void
    {
        $request = new Request('GET', 'https://example.com');
        $exception = new \RuntimeException('boom');

        $collection = $this->createMock(PsrHttpMessageParserCollection::class);
        $collection
            ->expects(self::once())
            ->method('parse')
            ->with($request, null, $exception)
            ->willReturn(['jsonPayload' => ['error' => 'boom']])
        ;

        $levelPicker = $this->createMock(LogLevelPickerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with('Request failed', self::callback(static function (array $context): bool {
                return $context['jsonPayload']['error'] === 'boom';
            }))
        ;

        $plugin = new LoggerPlugin($collection, $levelPicker, $logger);

        $this->expectExceptionObject($exception);

        $plugin
            ->handleRequest($request, static fn (): RejectedPromise => new RejectedPromise($exception), static fn () => null)
            ->wait()
        ;
    }

    public function testHandleRequestLogsCriticalWhenNextThrows(): void
    {
        $request = new Request('GET', 'https://example.com');
        $exception = new \RuntimeException('boom');

        $collection = $this->createMock(PsrHttpMessageParserCollection::class);
        $collection
            ->expects(self::once())
            ->method('parse')
            ->with($request, null, $exception)
            ->willReturn(['jsonPayload' => ['error' => 'boom']])
        ;

        $levelPicker = $this->createMock(LogLevelPickerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('critical')
            ->with('Request failed', self::callback(static function (array $context): bool {
                return $context['jsonPayload']['error'] === 'boom';
            }))
        ;

        $plugin = new LoggerPlugin($collection, $levelPicker, $logger);

        $this->expectExceptionObject($exception);

        $plugin->handleRequest($request, static function () use ($exception): never {
            throw $exception;
        }, static fn () => null);
    }
}
