<?php

namespace App\Providers;

use App\Tracing\CurrentSpanResolver;
use App\Tracing\JobPayloadTraceInjector;
use App\Tracing\JobSpanLinkListener;
use App\Tracing\SpanStore;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\TracerProviderInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpanStore::class);
        $this->app->singleton(CurrentSpanResolver::class);

        $this->app->singleton(TracerProviderInterface::class, function () {
            return Globals::tracerProvider();
        });
    }

    public function boot(
        QueueManager $queue,
        JobPayloadTraceInjector $payloadInjector,
        JobSpanLinkListener $listener,
    ): void {
        $queue->createPayloadUsing($payloadInjector);

        $this->app['events']->listen(JobProcessing::class, [$listener, 'before']);
        $this->app['events']->listen(JobProcessed::class, [$listener, 'after']);
        $this->app['events']->listen(JobFailed::class, [$listener, 'failed']);
    }
}
