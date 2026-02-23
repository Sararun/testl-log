<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\JsonFormatter;
use Psr\Log\LoggerInterface;

readonly class ObservabilityFormatter
{

    public function __construct(
        private TraceContextProcessor $traceContextProcessor,
        private MetadataProcessor $metadataProcessor,
        private JsonFormatter $formatter
    ) {
    }

    public function __invoke(LoggerInterface $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            // JSON формат — каждый лог = одна строка JSON.

            $this->formatter->includeStacktraces(true);
            $handler->setFormatter($this->formatter);

            // Добавляем trace_id/span_id из OpenTelemetry контекста.
            $handler->pushProcessor($this->traceContextProcessor);

            // Добавляем стандартные метаданные.
            $handler->pushProcessor($this->metadataProcessor);
        }
    }
}