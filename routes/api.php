<?php

use App\Http\Controllers\API\LanguageController;
use App\Http\Controllers\API\TranslationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::middleware('auth:sanctum')
    ->get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

Route::post('/login', [AuthController::class, 'login']);

 Route::middleware('auth:sanctum')
     ->group(
         static function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::apiResource('languages', LanguageController::class);
            Route::get('translations/export', [TranslationController::class, 'export']);
            Route::apiResource('translations', TranslationController::class);
         }
     );

