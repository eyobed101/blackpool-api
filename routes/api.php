<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::post('login', 'API\AuthController@login');
Route::post('register', [AuthController::class, 'create']);
Route::post('login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:api', 'json.response']], function(){
    Route::get('details', [AuthController::class, 'details']);
    Route::post('verifications', [VerificationController::class, 'uploadVerification']);

});
Route::group(['middleware' => ['auth:api', 'adminAuth', 'json.response']], function() {
    Route::get('admin/verifications/get', [VerificationController::class, 'adminVerifyUsers']);
    Route::post('admin/verifications/post', [VerificationController::class, 'adminActivateUser']);
});

