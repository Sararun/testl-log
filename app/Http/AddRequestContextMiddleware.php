<?php

namespace App\Http;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

class AddRequestContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = $request->header('X-Request-ID', Str::uuid7()->toString());

        Context::add('request_id', $requestId);
        Concurrency::extend()
        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
/*# Set the value to true to enable multiple data source feature
data_source.enabled: true

# Set the value to true to enable workspace feature
workspace.enabled: true

# Set the value to true to enable explore feature
explore.enabled: true*/