<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;

Route::post('data', [DataController::class, 'post']);
Route::put('data', [DataController::class, 'put']);
Route::put('data/checkpoint', [DataController::class, 'checkpoint']);
Route::patch('data', [DataController::class, 'patch']);
Route::delete('data', [DataController::class, 'delete']);

Route::get('auth/token', [AuthController::class, 'getToken']);
Route::get('auth/keys', [AuthController::class, 'getKeys']);
