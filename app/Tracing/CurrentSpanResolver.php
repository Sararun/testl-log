<?php

namespace App\Tracing;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;

/**
 * Обёртка над Span::getCurrent() для DI.
 * OTel PHP SDK хранит текущий span в статическом context —
 * это единственный способ его получить.
 */
class CurrentSpanResolver
{
    public function resolve(): SpanInterface
    {
        return Span::getCurrent();
    }
}
