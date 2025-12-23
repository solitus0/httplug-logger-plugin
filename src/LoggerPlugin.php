<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\VersionBridgePlugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPickerInterface;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection;

class LoggerPlugin implements Plugin
{
    use VersionBridgePlugin;

    public function __construct(
        private PsrHttpMessageParserCollection $collection,
        private LogLevelPickerInterface $levelPicker,
        private LoggerInterface $logger,
        private ?string $clientName = null,
    ) {
    }

    protected function doHandleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        try {
            return $next($request)->then(function (ResponseInterface $response) use ($request): ResponseInterface {
                $data = $this->collection->parse(request: $request, response: $response);

                if ($this->clientName !== null) {
                    $data['client_name'] = $this->clientName;
                }

                $logLevel = $this->levelPicker->pick(data: $data);

                match ($logLevel) {
                    'info' => $this->logger->info(message: 'Sending request', context: $data),
                    'warning' => $this->logger->warning(message: 'Bad request', context: $data),
                    'error' => $this->logger->error(message: 'Server error', context: $data),
                    'critical' => $this->logger->critical(message: 'Gateway error', context: $data),
                    default => null,
                };

                return $response;
            }, function (\Exception $e) use ($request): void {
                $data = $this->collection->parse(request: $request, throwable: $e);
                $this->logger->error(message: 'Request failed', context: $data);

                throw $e;
            });
        } catch (\Exception $exception) {
            $data = $this->collection->parse(request: $request, throwable: $exception);
            $this->logger->critical(message: 'Request failed', context: $data);

            throw $exception;
        }
    }
}
