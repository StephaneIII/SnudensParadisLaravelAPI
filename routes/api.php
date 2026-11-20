<?php

use App\Http\Controllers\API\AttributesValueController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

// public routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

// Public read routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/attributes-values', [AttributesValueController::class, 'index']);
Route::get('/attributes-values/{id}', [AttributesValueController::class, 'show']);

// protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/profile', [UserController::class, 'update']);

    // Product routes (admin only)
    Route::post('/products', [ProductController::class, 'create']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Order routes
    Route::get('/orders/{UID}', [OrderController::class, 'index']);
    Route::post('/orders/{UID}', [OrderController::class, 'create']);
    Route::post('/orders/{orderId}/items', [OrderController::class, 'addItem']);

    // Attributes Values routes (admin only)
    Route::post('/attributes-values', [AttributesValueController::class, 'create']);
    Route::put('/attributes-values/{id}', [AttributesValueController::class, 'update']);
    Route::delete('/attributes-values/{id}', [AttributesValueController::class, 'destroy']);
});
