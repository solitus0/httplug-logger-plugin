<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDecoratorProcessor implements ProcessorInterface
{
    private array $inMemoryCache = [];

    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->inMemoryCache) {
            $record['extra'] = array_merge($this->inMemoryCache, $record['extra']);

            return $record;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return $record;
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            $record['extra']['referer'] = addslashes($referer);
        }

        $route = $request->getPathInfo();
        if ($route !== '' && $route !== '0') {
            $record['extra']['route'] = $route;
        }

        $clientIp = $request->getClientIp();
        if ($clientIp) {
            $record['extra']['remoteIp'] = $clientIp;
        }

        $host = $request->getHttpHost();
        if ($host !== '' && $host !== '0') {
            $record['extra']['host'] = addslashes($host);
        }

        $userAgent = $request->headers->get('User-Agent');
        if ($userAgent) {
            $record['extra']['userAgent'] = $userAgent;
        }

        $this->inMemoryCache = $record['extra'];

        return $record;
    }
}
