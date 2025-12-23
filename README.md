# httplug-logger-plugin

Symfony bundle that logs HTTPlug requests/responses through Monolog, with optional Guzzle transfer stats and Google Cloud log formatting.

## Features
- HTTPlug plugin that logs request + response context for each client.
- Structured context (`httpRequest`, `jsonPayload`, `transfer_stats`) suitable for JSON logging.
- Log level selection based on response status (2xx info, 4xx warning, 500 error, >500 critical).
- Optional Guzzle transfer stats (latency + timing breakdown).
- Optional request/trace decorators for Google Cloud logging.

## Requirements
- PHP 8.1+
- Symfony 6.4+
- Monolog 3.7+
- HTTPlug (php-http/client-common)
- Guzzle 7 adapter (for transfer stats factory)

## Installation
```bash
composer require solitus0/httplug-logger-plugin
```

If you are not using Symfony Flex, register the bundle:
```php
return [
    Solitus0\HttplugLoggerBundle\LoggerBundle::class => ['all' => true],
];
```

## Basic Configuration
Define one or more logging plugins:
```yaml
# config/packages/httplug_logger.yaml
httplug_logger:
    plugins:
        api_client:
            parser_collection: httplug_logger.parser_collection.default
            level_picker: httplug_logger.level_picker.default
            logger: monolog.logger.http_client
```

Attach the plugin to an HTTPlug client:
```yaml
# config/packages/httplug.yaml
httplug:
    clients:
        api_client:
            plugins:
                - httplug_logger.plugin.api_client
```

## Transfer Stats (Guzzle 7)
To log transfer timing metrics, use the provided factory (it injects Guzzle `on_stats` to collect timing data):
```yaml
# config/packages/httplug.yaml
httplug:
    clients:
        api_client:
            factory: httplug_logger.guzzle7_factory.transfer_stats_aware
            plugins:
                - httplug_logger.plugin.api_client
```

When enabled, the log context includes:
- `transfer_stats`: `namelookup_time`, `connect_time`, `appconnect_time`, `pretransfer_time`, `starttransfer_time`, `total_time` (seconds)
- `httpRequest.latency`: formatted string like `0.123456s`

## Monolog Formatter (Google Cloud Logging)
The bundle provides a JSON formatter that reshapes records to Google Cloud Logging format:
```yaml
# config/packages/monolog.yaml
monolog:
    handlers:
        http_client:
            type: stream
            path: "php://stdout"
            formatter: monolog.formatter.gcp
            level: info
```

## Optional Log Decorators
Configure decorators that add extra metadata to logs:
```yaml
# config/packages/httplug_logger.yaml
httplug_logger:
    features:
        request_decorator: true
        gcp_trace_decorator: false
```

- `request_decorator` adds `referer`, `route`, `remoteIp`, `host`, `userAgent` to the log record `extra`.
- `gcp_trace_decorator` reads the `x-cloud-trace-context` header and adds a `logging.googleapis.com/trace` value.
  It requires `GOOGLE_CLOUD_PROJECT_ID` in your environment.

## Response Payload Truncation
Response payload logging is enabled by default and truncated if it exceeds a size limit:
```yaml
# config/packages/httplug_logger.yaml
httplug_logger:
    parser:
        response_payload_truncate: true
        response_payload_max_kilobyte_size: 7
```

Notes:
- If a response has a `content-disposition` header (e.g., file download), the payload is replaced with a placeholder message.
- JSON responses are decoded and logged as structured data when possible.

## GraphQL Request Payloads
A GraphQL request payload parser exists but is not part of the default parser collection. To enable it, define a custom parser collection service:
```yaml
# config/services.yaml
services:
    httplug_logger.parser_collection.graphql:
        class: Solitus0\HttplugLoggerBundle\Parser\PsrHttpMessageParserCollection
        autowire: true
        autoconfigure: true
        arguments:
            $parsers:
                - '@httplug_logger.parser.psr_request'
                - '@httplug_logger.parser.psr_request_graphql_payload'
                - '@httplug_logger.parser.psr_response'
                - '@httplug_logger.parser.psr_response_payload'
                - '@httplug_logger.parser.exception'
                - '@httplug_logger.parser.transfer_stats'
```

Then reference it in your plugin config:
```yaml
# config/packages/httplug_logger.yaml
httplug_logger:
    plugins:
        api_client:
            parser_collection: httplug_logger.parser_collection.graphql
            level_picker: httplug_logger.level_picker.default
            logger: monolog.logger.http_client
```

## Customization
- **Log level picker**: implement `Solitus0\HttplugLoggerBundle\LevelPicker\LogLevelPickerInterface` and point `level_picker` to your service.
- **Parsers**: implement `RequestParserInterface`, `ResponseParserInterface`, or `ExceptionParserInterface` and add to a custom `PsrHttpMessageParserCollection`.

## Logged Context Shape (Default)
The log record includes a message plus structured context. Shape example (values are illustrative placeholders):
```json
{
  "message": "Sending request",
  "context": {
    "httpRequest": {
      "requestMethod": "GET",
      "requestUrl": "https://example.com/resource",
      "requestSize": "123",
      "status": 200,
      "responseSize": "456",
      "latency": "0.123456s"
    },
    "jsonPayload": {
      "requestHeaders": {
        "Accept": "application/json",
        "Authorization": "REDACTED",
        "User-Agent": "YourApp/1.0"
      },
      "requestPayload": {"query": "..."},
      "responsePayload": {"data": {"id": 1}}
    },
    "transfer_stats": {
      "namelookup_time": 0.001,
      "connect_time": 0.012,
      "appconnect_time": 0.020,
      "pretransfer_time": 0.030,
      "starttransfer_time": 0.040,
      "total_time": 0.123
    },
    "client_name": "api_client"
  },
  "level": 200,
  "level_name": "INFO",
  "channel": "http_client",
  "datetime": "2025-01-01T12:00:00.000000+00:00",
  "extra": {
    "route": "/internal/endpoint",
    "remoteIp": "127.0.0.1",
    "host": "localhost",
    "userAgent": "Symfony BrowserKit"
  }
}
```
Notes:
- `extra` fields are added only when `features.request_decorator` or `features.gcp_trace_decorator` are enabled.
- `transfer_stats` and `httpRequest.latency` appear only when using the transfer-stats-aware Guzzle factory.
- Payloads are omitted or truncated based on parser settings and response headers.

## Testing and QA
- `make phpstan`
- `make ecs`
- `make rector`
- `make all`
