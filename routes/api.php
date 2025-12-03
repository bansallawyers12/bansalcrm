<?php

use Illuminate\Http\Request;

use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API routes for admin authentication
Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('throttle:api');
