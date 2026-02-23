<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class MetadataProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['service_name'] = config('app.name', 'laravel-crm');
        $extra['environment'] = config('app.env', 'production');
        $extra['php_sapi'] = PHP_SAPI;

        if (PHP_SAPI !== 'cli' && function_exists('request')) {
            try {
                $request = request();
                $extra['request_id'] = $request->header('X-Request-ID')
                    ?? $request->server('REQUEST_ID');
                $extra['request_method'] = $request->method();
                $extra['request_uri'] = $request->getRequestUri();
                $extra['client_ip'] = $request->ip();
            } catch (\Throwable $e) {
            }
        }

        return $record->with(extra: $extra);
    }
}