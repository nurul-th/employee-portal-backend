<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/ping', function () {
    return response()->json(['message' => 'API OK']);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

/* Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
*/

Route::middleware('auth:sanctum')->get('/debug-user', function (Request $request) {
    return $request->user();
});
