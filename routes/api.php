<?php

use Illuminate\Http\Request;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AppointmentController;

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

 
/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('throttle:api');

// List of nature of Enquiry
Route::get('/natureofenquiry', [AppointmentController::class, 'natureofenquiry'])->withoutMiddleware('throttle:api');

// List of Service Type
Route::get('/servicetype', [AppointmentController::class, 'servicetype'])->withoutMiddleware('throttle:api');

Route::prefix('appointments')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->withoutMiddleware('throttle:api'); // List
        Route::post('/', [AppointmentController::class, 'store'])->withoutMiddleware('throttle:api'); // Create
        Route::get('/{id}', [AppointmentController::class, 'show'])->withoutMiddleware('throttle:api'); // Show
        Route::put('/{id}', [AppointmentController::class, 'update'])->withoutMiddleware('throttle:api'); // Update
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->withoutMiddleware('throttle:api'); // Delete
        Route::post('logout', [AuthController::class, 'logout'])->withoutMiddleware('throttle:api');
    });
});
