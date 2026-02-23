<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    \App\Models\User::query()->truncate();
    $str = \Illuminate\Support\Str::uuid7()->toString();
    $bytes = random_int(1,8823);
    \Illuminate\Support\Facades\Cache::put($str, "$bytes");
    \Illuminate\Support\Facades\Log::info('create user');
    $users = \App\Models\User::factory()->createMany(10);
    return view('welcome');
});

Route::get('/dashboard', function () {

});
