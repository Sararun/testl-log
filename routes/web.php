<?php

use App\DoJob;
use App\SortUseCase;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    \App\Models\User::query()->truncate();
    $str = \Illuminate\Support\Str::uuid7()->toString();
    $bytes = random_int(1,8823);
    \Illuminate\Support\Facades\Cache::put($str, "$bytes");
    \Illuminate\Support\Facades\Log::info('create user', [
        'context' => 'new context'
    ]);
    $users = \App\Models\User::factory()->createMany(10);
    DoJob::dispatch()->onQueue('pim');
    return new \Illuminate\Http\JsonResponse();
});

Route::get('/dashboard', function () {

});

Route::get('/external-post', function () {
    $tracer = \OpenTelemetry\API\Globals::tracerProvider()->getTracer('laravel-crm');

    $payload = [
        'title'  => 'Observability test post',
        'body'   => 'Sent from Laravel with trace context: ' . \Illuminate\Support\Str::uuid7()->toString(),
        'userId' => 1,
    ];

    $span = $tracer->spanBuilder('jsonplaceholder.post')->startSpan();
    $scope = $span->activate();

    $span->setAttribute('request.title', $payload['title']);
    $span->setAttribute('request.body', $payload['body']);

    $response = \Illuminate\Support\Facades\Http::post('https://jsonplaceholder.typicode.com/posts', $payload);
    $result = $response->json();

    $span->setAttribute('response.status_code', $response->status());
    $span->setAttribute('response.id', $result['id'] ?? null);
    $span->setAttribute('response.body', json_encode($result));

    $span->end();
    $scope->detach();

    return response()->json($result);
});
