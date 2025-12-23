<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GoogleCloudTraceDecoratorProcessor implements ProcessorInterface
{
    private ?string $inMemoryCache = null;

    public function __construct(
        private RequestStack $requestStack,
        private string $googleCloudProject
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->inMemoryCache) {
            $record['extra']['trace'] = $this->inMemoryCache;

            return $record;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return $record;
        }

        $traceContext = $request->headers->get('x-cloud-trace-context');
        if ($traceContext && str_contains($traceContext, '/')) {
            $traceId = explode('/', $traceContext)[0];
            $trace = sprintf('projects/%s/traces/%s', $this->googleCloudProject, $traceId);
            $record['extra']['trace'] = $trace;
            $this->inMemoryCache = $trace;
        }

        return $record;
    }
}
