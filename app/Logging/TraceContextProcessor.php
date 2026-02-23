<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

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
        }

        $extra = $record->extra;
        $extra['trace_id'] = $traceId;
        $extra['span_id'] = $spanId;

        return $record->with(extra: $extra);
    }
}