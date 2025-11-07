<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\NodeController;
use App\Http\Controllers\Api\ProjectController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // User routes
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
    });

    // Project generation routes
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{projectId}', [ProjectController::class, 'show']);
    Route::get('projects/{projectId}/links', [LinkController::class, 'index']);
    Route::get('projects/{projectId}/export', [ProjectController::class, 'export']);

    // Node manipulation routes
    Route::post('nodes/{nodeId}/brief', [NodeController::class, 'regenerate']);
    Route::post('nodes/{nodeId}/promote', [NodeController::class, 'promote']);
    Route::post('nodes/{nodeId}/demote', [NodeController::class, 'demote']);
    Route::post('nodes/merge', [NodeController::class, 'merge']);
    Route::post('nodes/split', [NodeController::class, 'split']);
});
