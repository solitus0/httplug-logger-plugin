<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\Monolog\Formatter;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class GoogleCloudJsonFormatter extends JsonFormatter
{
    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);
        $normalized['severity'] = $normalized['level_name'];
        $normalized['time'] = $record->datetime->format(\DateTimeInterface::RFC3339_EXTENDED);

        $extra = $this->getProperty($normalized, 'extra', []);
        $context = $normalized['context'] ?? [];

        if (!empty($extra['trace'])) {
            $normalized['logging.googleapis.com/trace'] = $extra['trace'];
            $normalized['logging.googleapis.com/trace_sampled'] = true;
            unset($extra['trace']);
        }

        $httpRequestData = $this->getProperty($context, 'httpRequest', []);
        if ($httpRequestData) {
            if (!empty($extra['remoteIp'])) {
                $httpRequestData['remoteIp'] = $extra['remoteIp'];
                unset($extra['remoteIp']);
            }

            $normalized['httpRequest'] = $httpRequestData;
            unset($context['httpRequest']);
        }

        $jsonPayload = $this->getProperty($context, 'jsonPayload', []);
        unset($context['jsonPayload']);

        $normalized = array_merge($normalized, $context, $extra, $jsonPayload);
        unset($normalized['level'], $normalized['level_name'], $normalized['datetime'], $normalized['extra'], $normalized['context']);

        ksort($normalized);

        return $normalized;
    }

    public function format(LogRecord $record): string
    {
        try {
            $this->maxNormalizeDepth = 20;
            $normalized = $this->normalizeRecord($record);

            return $this->toJson($normalized, true) . ($this->appendNewline ? "\n" : '');
        } catch (\Exception $exception) {
            return parent::format($record);
        }
    }

    private function getProperty($data, string $key, $default = null)
    {
        if (!is_array($data)) {
            return $default;
        }

        return (array_key_exists($key, $data) && null !== $data[$key]) ? $data[$key] : $default;
    }
}
