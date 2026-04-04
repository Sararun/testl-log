<?php

namespace App\Tracing;

/**
 * Callable для Queue::createPayloadUsing().
 * Инжектит trace context в payload каждого job при dispatch.
 */
final readonly class JobPayloadTraceInjector
{
    public function __construct(
        private CurrentSpanResolver $spanResolver,
    ) {}

    public function __invoke(string $connection, ?string $queue, array $payload): array
    {
        $ctx = $this->spanResolver->resolve()->getContext();

        if ($ctx->isValid()) {
            $payload['otel_link'] = [
                'trace_id' => $ctx->getTraceId(),
                'span_id'  => $ctx->getSpanId(),
                'flags'    => $ctx->getTraceFlags(),
            ];
        }

        return $payload;
    }
}
