<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'API OK']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
