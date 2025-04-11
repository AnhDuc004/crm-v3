<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('login', [LoginController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('logout', [ProfileController::class, 'logout']);
    Route::put('change-password', [ProfileController::class, 'changePassword']);
    Route::put('admin/reset-password/{id}', [ProfileController::class, 'resetPassword']);
    Route::get('roles', [ProfileController::class, 'roles']);
    Route::get('permissions', [ProfileController::class, 'permissions']);
    Route::get('staff/{id}/allpermissions', [ProfileController::class, 'StaffPermissions']);
});
