<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\LevelPicker;

class LogLevelPicker implements LogLevelPickerInterface
{
    public function pick(array $data): string
    {
        $statusCode = $data['httpRequest']['status'] ?? 0;
        if ($statusCode >= 200 && $statusCode < 300) {
            return 'info';
        }

        if ($statusCode >= 400 && $statusCode < 500) {
            return 'warning';
        }

        if ($statusCode === 500) {
            return 'error';
        }

        if ($statusCode > 500) {
            return 'critical';
        }

        return 'info';
    }
}
