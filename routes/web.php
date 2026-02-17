<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    \Illuminate\Support\Facades\Log::info('Hello world', [123, 'priority' => 1, 'help' => true]);
    return view('welcome');
});

Route::get('/dashboard', function () {

});
