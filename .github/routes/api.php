<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Potpuno javne rute za goste (neulogovane)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Zasticene rute - zahtevaju Bearer Token (Sanctum middleware)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Minimalni zahtev: Jedna resource ruta koja pokriva kompletan CRUD
    Route::apiResource('appointments', AppointmentController::class);
    
});