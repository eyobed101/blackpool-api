<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Exception;

class AgentController extends Controller
{
    //
    public function GetAllReferedUsers()
    {
         // lets get all the users that have the admin id of this agent
         try {
            $admin_id = Auth::user()->id;
            $user_list = User::with('bet', 'transaction')->where('admin_id', '=', $admin_id)->get();
            $pending_users = User::where('verification_status', '=', 'ONBOARDING')->where('role', '=', 'USER')->where('admin_id', '=', $admin_id)->get();
            $verified_users = User::where('verification_status', '=', 'VERIFIED')->where('role', '=', 'USER')->where('admin_id', '=', $admin_id)->get();
            $disabled_users = User::where('verification_status', '=', 'DISABLED')->where('role', '=', 'USER')->where('admin_id', '=', $admin_id)->get();
            return response()->json(["pending_users" => count($pending_users), "verified_users" => count($verified_users), "disabled_users" => count($disabled_users), 'customers' => $user_list], 200);
         } catch(Exception $e) {
              Log::info($e->getMessage());
              return response()->json(['error' => 'something went wrong'], 500);
         }
    }
    public function GetUsersDeposit()
    {
        try {
             $admin_id = Auth::user()->id;
             $total_success_deposit = 0;
             $total_failed_deposit = 0;
             $total_pending_deposit = 0;
             $transaction_list  = Transaction::with('user')->where('type', '=', 'Deposit')->whereHas('user', function($query) use ($admin_id) {
                     $query->where("admin_id", "=", $admin_id);
             })->get();
             $success_deposit = Transaction::where('type', '=', 'DEPOSIT')->where('status', '=', 'SUCCESS')->whereHas('user', function($query) use ($admin_id) {
               $query->where("admin_id", "=", $admin_id);
             })->get();
             forEach($success_deposit as $deposit) {
               // lets add all the amount together
               $total_success_deposit = $total_success_deposit + $deposit->amount;
              }
             $failed_deposit = Transaction::where('type', '=', 'DEPOSIT')->where('status', '=', 'FAILED')->whereHas('user', function($query) use ($admin_id) {
               $query->where("admin_id", "=", $admin_id);
             })->get();
             forEach($failed_deposit as $deposit) {
               $total_failed_deposit = $total_failed_deposit + $deposit->amount;
              }
             $pending_deposit =  Transaction::where('type', '=', 'DEPOSIT')->where('status', '=', 'PENDING')->whereHas('user', function($query) use ($admin_id) {
               $query->where("admin_id", "=", $admin_id);
       })->get();
       forEach($pending_deposit as $deposit) {
          $total_pending_deposit = $total_pending_deposit +$deposit->amount;
     }
             return response()->json(["success"=>$total_success_deposit,  "pending"=>$total_pending_deposit, "failed" => $total_failed_deposit, "deposits" => $transaction_list], 200);
        } catch(Exception $e) {
             Log::info($e->getMessage());
             return response()->json(["error" => "Something went wrong please try again"], 500);
        }
    }
    public function GetUsersWithdrawal()
    {
     try {
          $admin_id = Auth::user()->id;
          $total_success_withdrawal = 0;
          $total_failed_withdrawal = 0;
          $total_pending_withdrawal = 0;
          $transaction_list  = Transaction::with('user')->where('type', '=', 'WITHDRAW')->whereHas('user', function($query) use ($admin_id) {
                  $query->where("admin_id", "=", $admin_id);
          })->get();
          $success_withdraw = Transaction::where('type', '=', 'WITHDRAW')->where('status', '=', 'SUCCESS')->whereHas('user', function($query) use ($admin_id) {
            $query->where("admin_id", "=", $admin_id);
           })->get();
           foreach($success_withdraw as $withdrawal) {
               $total_success_withdrawal = $total_success_withdrawal + $withdrawal->amount;
         }
          $failed_withdraw = Transaction::where('type', '=', 'WITHDRAW')->where('status', '=', 'FAILED')->whereHas('user', function($query) use ($admin_id) {
            $query->where("admin_id", "=", $admin_id);
    })->get();
          foreach($failed_withdraw as $withdrawal) {
               $total_failed_withdrawal = $total_failed_withdrawal + $withdrawal->amount;
          }
          $pending_withdraw =  Transaction::where('type', '=', 'WITHDRAW')->where('status', '=', 'PENDING')->whereHas('user', function($query) use ($admin_id) {
            $query->where("admin_id", "=", $admin_id);
    })->get();
          foreach($pending_withdraw as $pending) {
               $total_pending_withdrawal = $total_pending_withdrawal + $pending->amount;
          }
          return response()->json(["success"=>$total_success_withdrawal,  "pending"=>$total_pending_withdrawal, "failed" => $total_failed_withdrawal, "withdrawals" => $transaction_list], 200);
     } catch(Exception $e) {
          Log::info($e->getMessage());
          return response()->json(["error" => "Something went wrong please try again"], 500);
     }
    }
}
