<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * MetadataProcessor — добавляет стандартные поля для фильтрации в OpenSearch.
 *
 * Поля:
 * - service_name: имя приложения (для мульти-сервисной среды)
 * - environment: local/staging/production
 * - php_sapi: fpm-fcgi/cli (для разделения web и CLI логов)
 * - request_id, request_method, request_uri, client_ip (только для HTTP)
 *
 * Monolog 3: принимает и возвращает LogRecord (иммутабельный объект).
 */
class MetadataProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        $context['service.name'] = config('app.name', 'laravel');
        $context['deployment.environment'] = config('app.env', 'production');
        $context['php.sapi'] = PHP_SAPI;

        if (PHP_SAPI !== 'cli' && function_exists('request')) {
            try {
                $request = request();

                $context['http.request.id'] = $request->header('X-Request-ID')
                    ?? $request->server('REQUEST_ID');

                $context['http.request.method'] = $request->method();
                $context['url.path'] = $request->getRequestUri();
                $context['client.address'] = $request->ip();

            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $record->with(context: $context);
    }
}
