<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;

Route::post('data', [DataController::class, 'postBatch']);
Route::put('data', [DataController::class, 'putBatch']);
Route::put('data/checkpoint', [DataController::class, 'putCheckpoint']);
Route::patch('data', [DataController::class, 'patchBatch']);
Route::delete('data', [DataController::class, 'deleteBatch']);

Route::get('auth/token', [AuthController::class, 'getToken']);
Route::get('auth/keys', [AuthController::class, 'getKeys']);
