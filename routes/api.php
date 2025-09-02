<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/theme/{prefix}', [\App\Http\Controllers\ThemeController::class, 'getTheme']);
Route::get('/tenantTheme/{id}', [\App\Http\Controllers\ThemeController::class, 'getTenantTheme']);
Route::post('/updateTheme', [\App\Http\Controllers\ThemeController::class, 'updateTheme']);
Route::post('/tenant_register', [\App\Http\Controllers\TenantController::class, 'register']);
Route::get('/tenants', [\App\Http\Controllers\TenantController::class, 'index']);
Route::post('/tenants/{id}/delete', [\App\Http\Controllers\TenantController::class, 'destroy']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
