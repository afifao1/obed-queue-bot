<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/telegram/webhook', function () {
    app(Nutgram::class)->run();
});

