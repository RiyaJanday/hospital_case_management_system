<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedicalCaseController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/cases', [MedicalCaseController::class, 'index']);
    Route::post('/cases', [MedicalCaseController::class, 'store']);
    Route::get('/cases/{id}', [MedicalCaseController::class, 'show']);
    Route::put('/cases/{id}', [MedicalCaseController::class, 'update']);
    Route::delete('/cases/{id}', [MedicalCaseController::class, 'destroy']);
});
