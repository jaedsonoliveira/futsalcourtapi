<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourtController;


Route::get('/ping', function(){
    return ['pong'=>true];
});

Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);
Route::post('auth/refresh', [AuthController::class,'refresh']);
Route::post('auth/user', [AuthController::class, 'create']);

Route::get('user', [UserController::class, 'getUser']);
Route::put('user', [UserController::class, 'editUser']);
Route::get('user/appointments', [UserController::class, 'getAppointments']);

Route::get('courts', [CourtController::class, 'getAll']);
Route::get('court/{id}', [CourtController::class, 'getOne']);
Route::post('court/{id}/appointments', [CourtController::class, 'setAppointments']);

