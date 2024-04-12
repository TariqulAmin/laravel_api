<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\OAuthController;

// Protected Routes

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('/tasks', TaskController::class);
});

// Public Routes

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/get-token', [OAuthController::class, 'generateToken'])->name('generate.token');
Route::post('/get-token', [OAuthController::class, 'successToken'])->name('token.success');

