<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/resend', [UserController::class, 'resend']);
Route::post('/logout', [UserController::class, 'logout']);
Route::get('email/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');
Route::post('/reset', [UserController::class, 'reset']);
Route::post('/reset/password', [UserController::class, 'resetPassword']);
Route::get('password/reset/{token}', [UserController::class, 'returnToFrontEnd'])->name('password.reset');

Route::group(['middleware' => 'checkAuth'], function () {
    Route::get('/droplets', [DashboardController::class, 'droplets']);
});

Route::any('/', [UserController::class, 'default']);
