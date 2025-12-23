<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle\LevelPicker;

interface LogLevelPickerInterface
{
    public function pick(array $data): string;
}
