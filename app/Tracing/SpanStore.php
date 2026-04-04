<?php

namespace App\Tracing;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\ScopeInterface;

/**
 * Хранит активные span/scope для job'ов между Queue::before и Queue::after.
 * Singleton в контейнере — без статики.
 */
final class SpanStore
{
    /** @var array<string, array{SpanInterface, ScopeInterface}> */
    private array $spans = [];

    public function put(string $jobId, SpanInterface $span, ScopeInterface $scope): void
    {
        $this->spans[$jobId] = [$span, $scope];
    }

    /**
     * @return array{SpanInterface, ScopeInterface}|null
     */
    public function pull(string $jobId): ?array
    {
        $entry = $this->spans[$jobId] ?? null;
        unset($this->spans[$jobId]);

        return $entry;
    }
}