<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;


/**
 * ObservabilityFormatter — форматирует логи для Fluent Bit → OpenSearch pipeline.
 *
 * Что делает:
 * 1. JSON формат — каждый лог = одна строка JSON (для парсинга Fluent Bit)
 * 2. Добавляет trace_id и span_id из OTel контекста (корреляция с Jaeger)
 * 3. Добавляет service_name, environment, request_id, client_ip
 *
 * Использование в config/logging.php:
 *   'tap' => [\App\Logging\ObservabilityFormatter::class],
 */
readonly class ObservabilityFormatter
{

    public function __construct(
        private TraceContextProcessor $traceContextProcessor,
        private MetadataProcessor $metadataProcessor,
    ) {
    }


    /**
     * @param \Monolog\Logger $logger
     * @return void
     */
    public function __invoke( mixed $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {

            $formatter = new JsonFormatter(
                JsonFormatter::BATCH_MODE_JSON,
                true // append newline
            );

            $formatter->includeStacktraces(true);

            $handler->setFormatter($formatter);

            $handler->pushProcessor($this->traceContextProcessor);
            $handler->pushProcessor($this->metadataProcessor);
        }
    }
}