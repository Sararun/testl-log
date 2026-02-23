<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * TraceContextProcessor — извлекает trace_id и span_id из текущего OTel Span.
 *
 * Корреляция логов и трейсов:
 * - Лог содержит trace_id
 * - В Grafana кликаешь на лог → попадаешь в Jaeger-трейс
 * - В Jaeger видишь трейс → ищешь логи с тем же trace_id
 *
 * Если OTel extension не установлен — trace_id/span_id будут null.
 *
 * Monolog 3: принимает и возвращает LogRecord (иммутабельный объект).
 */
class TraceContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $traceId = null;
        $spanId = null;

        try {
            if (class_exists(\OpenTelemetry\API\Trace\Span::class)) {
                $span = \OpenTelemetry\API\Trace\Span::getCurrent();
                $context = $span->getContext();

                $traceId = $context->getTraceId();
                $spanId = $context->getSpanId();

                if ($traceId === '00000000000000000000000000000000') {
                    $traceId = null;
                }
                if ($spanId === '0000000000000000') {
                    $spanId = null;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $context = $record->context;
        $context['trace_id'] = $traceId;
        $context['span_id'] = $spanId;

        return $record->with(context: $context);
    }
}