<?php

declare(strict_types=1);

namespace Solitus0\HttplugLoggerBundle;

use Psr\Log\LoggerInterface;
use Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPickerInterface;
use Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection;

class LoggerPluginFactory
{
    public static function build(
        PsrHttpMessageParserCollection $collection,
        LogLevelPickerInterface $levelPicker,
        LoggerInterface $logger,
        ?string $clientName = null,
    ): LoggerPlugin {
        return new LoggerPlugin(
            collection: $collection,
            levelPicker: $levelPicker,
            logger: $logger,
            clientName: $clientName,
        );
    }
}
