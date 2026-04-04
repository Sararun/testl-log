<?php

namespace App\Tracing;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerProviderInterface;

final readonly class JobSpanLinkListener
{
    public function __construct(
        private TracerProviderInterface $tracerProvider,
        private SpanStore $spanStore,
    ) {}

    public function before(JobProcessing $event): void
    {
        $link = $event->job->payload()['otel_link'] ?? null;

        if (!$link) {
            return;
        }

        $tracer = $this->tracerProvider->getTracer('laravel');
        $linkCtx = SpanContext::create($link['trace_id'], $link['span_id'], $link['flags']);

        $span = $tracer->spanBuilder($event->job->resolveName())
            ->addLink($linkCtx)
            ->startSpan();
        $scope = $span->activate();

        $this->spanStore->put($event->job->getJobId(), $span, $scope);
    }

    public function after(JobProcessed $event): void
    {
        $this->endSpan($event->job->getJobId());
    }

    public function failed(JobFailed $event): void
    {
        $entry = $this->spanStore->pull($event->job->getJobId());

        if (!$entry) {
            return;
        }

        [$span, $scope] = $entry;

        if ($event->exception) {
            $span->recordException($event->exception);
        }

        $span->setStatus(StatusCode::STATUS_ERROR, $event->exception?->getMessage() ?? 'Job failed');
        $span->end();
        $scope->detach();
    }

    private function endSpan(string $jobId): void
    {
        $entry = $this->spanStore->pull($jobId);

        if (!$entry) {
            return;
        }

        [$span, $scope] = $entry;
        $span->end();
        $scope->detach();
    }
}
