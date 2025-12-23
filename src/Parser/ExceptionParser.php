<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Parser;

class ExceptionParser implements ExceptionParserInterface
{
    public function parse(\Exception $e): array
    {
        return [
            'httpRequest' => [
                'status' => 500,
            ],
            'jsonPayload' => [
                'exception' => [
                    'code' => $this->getCode($e),
                    'message' => $this->getMessage($e),
                    'class' => $this->getExceptionClass($e),
                    'context' => $this->getContext($e),
                ],
            ],
        ];
    }

    final protected function getCode(\Exception $e): int
    {
        return $e->getCode();
    }

    final protected function getMessage(\Exception $e): string
    {
        return $e->getMessage();
    }

    final protected function getExceptionClass(\Exception $e): string
    {
        return get_class($e);
    }

    final protected function getContext(\Exception $e): array
    {
        return method_exists($e, 'getHandlerContext') ? $e->getHandlerContext() : [];
    }
}
