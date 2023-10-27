<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\TransactionController;



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
    Route::post('deposit', [TransactionController::class, 'userAccountDeposit']);
    Route::post('withdraw', [TransactionController::class, 'userRequestWithdrawal']);
});
Route::group(['middleware' => ['auth:api', 'adminAuth', 'json.response']], function() {
    Route::get('admin/verifications/get', [VerificationController::class, 'adminVerifyUsers']);
    Route::post('admin/verifications/post', [VerificationController::class, 'adminActivateUser']);
    Route::post('admin/verifications/disable', [VerificationController::class, 'adminDisableUser']);
    Route::get("admin/deposit/get", [TransactionController::class, 'adminGetPendingDeposits']);
    Route::post("admin/deposit/approve", [TransactionController::class, 'adminApproveDeposit']);
    Route::post("admin/deposit/disapprove", [TransactionController::class, 'adminDisapproveDeposit']);
    Route::get("admin/withdrawals/get", [TransactionController::class, 'adminGetWithdrawalRequests']);
    Route::post("admin/withdrawals/approve", [TransactionController::class, 'adminApproveWithdrawalRequest']);
    Route::get("admin/users/get", [AuthController::class,'GetAllUsers']);
});

