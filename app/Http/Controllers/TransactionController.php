<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TransactionController extends Controller
{
    //
    public function userAccountDeposit(Request $request)
    {
           try {
              // lets ceate the user account deposit
              $validator  = Validator::make($request->all(), [
                  'amount' => 'required',
                  'crypto_type' => 'required',
             ]);
                if  ($validator->fails()) {
                    return response()->json(['error'  => $validator->errors()], 500);
                }
                if (!$request->hasFile('transaction_image')) {
                     return response()->json(['error' => "please upload image"], 500);
                }
                $user_id = Auth::user()->id;
                // lets check if the user is verified first
                if(Auth::user()->verification_status == "ONBOARDING")
                {
                     return response()->json(['error' => 'user not verified']);
                }
                $time_now = Carbon::now()->getTimestamp();
                $transaction_image = $request->file('transaction_image'); // lets get the uploaded file
                $filename = strval($time_now) . '-' . $transaction_image->getClientOriginalName();
                $transaction = Transaction::create([
                    'amount' => $request->amount,
                    'crypto_type' => $request->crypto_type,
                    'type' => "DEPOSIT",
                    'image' => $request->file('transaction_image')->storeAs('user/receipts', $filename),
                    'user_id'  => $user_id,
                    'status' => "PENDING"
                ]);
                return response()->json(["transaction" => $transaction], 200);

           } catch(Exception $e) {
            return response()->json(["error" => "something went wrong please try again"], 500);
              Log::info($e->getMessage());
           }
    }
    public function adminGetPendingDeposits(Request $request)
    {
          try {
               $pending_transactions = Transaction::where("status", "=", "PENDING")->where('type', '=', 'DEPOSIT')->with('user')->get();
               return response()->json(["transactions" => $pending_transactions]);
          } catch(Exception $e) {
            Log::error($e->getMessage());
            return response()->json(["error" => "something went wrong"], 500);
          }
    }
    public function adminApproveDeposit(Request $request)
    {
         try {
              $validator =  Validator::make($request->all(), [
                  'transaction_id' => 'required'
              ]);
              if  ($validator->fails()) {
                         return response()->json(['error'  => $validator->errors()], 500);
              }
              $transaction = Transaction::findOrFail($request->transaction_id);
              if($transaction->status == "SUCCESS" || $transaction->status == "FAILED" )
              {
                    return response()->json(["error" => "transaction already taken action on"]);
              }
              $transaction->status = "SUCCESS";
              $transaction->save();
              $user = User::findOrFail($transaction->user_id);
              $user->balance =$user->balance + $transaction->amount;
              $user->save();
              return response()->json(["transaction" => $transaction]);
         } catch(Exception $e) {
             Log::error($e->getMessage());
             return response()->json(["error" => 'something went wrong'], 500);
         }
    }
    public function adminDisapproveDeposit(Request $request)
    {
          try {
            $validator =  Validator::make($request->all(), [
                'transaction_id' => 'required'
            ]);
            if($validator->fails()) {
                  return response()->json(['error' => $validator->error()], 500);
            }
            $transaction = Transaction::findOrFail($request->transaction_id);
            if($transaction->status != "PENDING")
            {
                  return response()->json(["error" => 'transaction already taken action on']);
            }
            $transaction->status = "FAILED";
            $transaction->save();
            return response()->json(["transaction" => $transaction]);
          } catch(Exception $e) {
                Log::error($e->getMessage());
                return response()->json(["error" => "something went wrong please try again"]);
          }
    }
    // user request withdrawal
    public function userRequestWithdrawal(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                    'wallet_address' => 'required',
                    'amount' => 'required',
                    'crypto_type' => 'required',

            ]);
            if($validator->fails())
            {
                  return response()->json(['error' => $validator->error()]);
            }
            $user = Auth::user();
            if($user->verification_status == "DISABLED")
            {
                  return response()->json(['error' => "your account is disabled"]);
            }
            if($user->balance < $request->amount)
            {
                 return response()->json(['error' => 'Insufficient amount ' . strval($user->balance) . ' < ' . strval($request->amount)]);
            }
            $new_withdraw_request = Transaction::create([
                   'wallet_address' => $request->wallet_address,
                   'amount' => $request->amount,
                   'crypto_type' => $request->crypto_type,
                   'type' => 'WITHDRAW',
                   'user_id' => $user->id,
                   'status' => 'PENDING'
            ]);
            // lets return the response
            return response()->json($new_withdraw_request, 200);
        } catch(Exception $e) {
                 Log::error($e->getMessage());
                 return response()->json(['error' => 'something went wrong'], 500);
        }
    }
    public function adminGetWithdrawalRequests()
    {
         try {
             $withdraw_requests = Transaction::where('status', '=', 'PENDING')->where('type', '=', 'WITHDRAW')->get();
             // lets return the requests
             return response()->json(['transactions' => $withdraw_requests], 200);

         } catch (Exception $e) {
              Log::error($e->getMessage());
              return response()->json(['error' => 'something went wrong try again']);
         }
    }
    public function adminApproveWithdrawalRequest(Request  $request)
    {
         try {
              $validator = Validator::make($request->all(), [
                  "transaction_id"  => "required"
              ]);
              if($validator->fails()) {
                  return response()->json(["error" => $validator->error()]);
              }
              $withdraw_requests = Transaction::find($request->transaction_id);
              if(is_null($withdraw_requests))
              {
                  return response()->json(["error" => "withdraw request doesn't exist"], 404);
              }
              if($withdraw_requests->status != "PENDING")
              {
                   return response()->json(["error" => "request not pending"], 404);
              }
              $withdraw_requests->status = "SUCCESS";
              $withdraw_requests->save();
              return response()->json(["transactions" => $withdraw_requests], 200);
         } catch (Exception $e) {
              Log::info($e->getMessage());
              return response(["error" => "something went wrong"]);
         }
    }
}
