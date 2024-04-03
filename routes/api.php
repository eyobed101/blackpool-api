<?php

use App\Http\Controllers\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SportController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\BetHistoryController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\WalletController;
use Illuminate\Database\Events\TransactionCommitted;

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
Route::post('send-otp', [AuthController::class, 'SendOTP']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/{admin_id}/user/register', [AuthController::class, 'createUserWithAdminId']);
Route::group(['middleware' => ['auth:api', 'json.response']], function () {
    Route::get('details', [AuthController::class, 'details']);
    Route::post('verifications', [VerificationController::class, 'uploadVerification']);
    Route::get('profileinfo', [VerificationController::class, 'fetchVerificationDetails']);
    Route::post('updateinfo', [VerificationController::class, 'changeProfile']);
    Route::post('deposit', [TransactionController::class, 'userAccountDeposit']);
    Route::post('withdraw', [TransactionController::class, 'userRequestWithdrawal']);
    Route::get('history', [TransactionController::class, 'userGetHistory']);
    Route::delete('deleteaccount', [AuthController::class, 'delete']);
    Route::put('changepassword', [AuthController::class, 'changePassword']);
    Route::post('placebet',  [BetController::class, 'placeBet']);
    Route::post('settlement',  [Settlement::class, 'checkBetOutcome']);
    Route::get('/bet-history', [BetHistoryController::class, 'index']);
   
});
Route::group(['middleware' => ['auth:api','cors', 'superAdminAuth', 'json.response']], function () {
    Route::get('admin/verifications/get', [VerificationController::class, 'adminVerifyUsers']);
    Route::post('admin/verifications/post', [VerificationController::class, 'adminActivateUser']);
    Route::post('admin/verifications/disable', [VerificationController::class, 'adminDisableUser']);
    Route::get("admin/deposit/get", [TransactionController::class, 'adminGetPendingDeposits']);
    Route::post("admin/deposit/approve", [TransactionController::class, 'adminApproveDeposit']);
    Route::post("admin/deposit/disapprove", [TransactionController::class, 'adminDisapproveDeposit']);
    Route::get("admin/withdrawals/get", [TransactionController::class, 'adminGetWithdrawalRequests']);
    Route::post("admin/withdrawals/approve", [TransactionController::class, 'adminApproveWithdrawalRequest']);
    Route::post("admin/withdrawals/disapprove", [TransactionController::class, 'adminDisapproveWithdrawalRequest']);
    Route::get('admin/transactions/get', [TransactionController::class, 'admnGetAllTransactions']);
    Route::get("admin/users/get", [AuthController::class, 'GetAllUsers']);
    Route::post("admin/agent/create", [AuthController::class, 'superAdminCreateAdmin']);
    Route::get("admin/agent/list", [AuthController::class, 'showAllAdmins']);
    Route::get("admin/deposit/all/get", [TransactionController::class, 'adminGetAllDeposits']);
    Route::get("admin/withdrawals/all/get", [TransactionController::class, 'adminGetAllWithdrawals']);
    Route::get("admin/agents/get/latest", [AuthController::class, 'GetLatestAdmins']);
    Route::post("admin/wallets/create", [WalletController::class, 'createWalletAddress']);
    Route::get("admin/wallets/get", [WalletController::class, 'getWalletAddresses']);
    Route::post("admin/wallets/setDefault", [WalletController::class, 'setDefaultWalletAddress']);
    Route::post("admin/users/addBonus", [AuthController::class, 'AddBonusToUser']);
    Route::post("admin/profile/changePassword", [AuthController::class, 'changePassword']);
});
Route::group(['middleware' => ['auth:api', 'adminAuth', 'json.response']], function(){
     // lets create the routes of the admin
     Route::get("agent/users/list", [AgentController::class, 'GetAllReferedusers']);
     Route::get("agent/users/deposit", [AgentController::class, 'GetUsersDeposit']);
     Route::get("agent/users/withdraw", [AgentController::class, 'GetUsersWithdrawal']);
});
Route::get('/games', [SportController::class, 'getGames']);
Route::get("/wallets/getDefault", [WalletController::class, 'getDefaultWalletAddress']);
Route::get('/scores', [ScoreController::class, 'getScores']);




