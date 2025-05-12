<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register'])->name('register');;
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');;

    Route::get('/test', [TranslationController::class, 'test']);

Route::middleware('auth:sanctum')->prefix('translations')->group(function () {
    Route::get('/export', [TranslationController::class, 'export']);    
    Route::get('/', [TranslationController::class, 'search']);         
            Route::post('/', [TranslationController::class, 'store']);         
            Route::get('/list/{locale}', [TranslationController::class, 'index']);  
            Route::delete('/{id}', [TranslationController::class, 'destroy']); 
});


