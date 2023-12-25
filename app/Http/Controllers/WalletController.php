<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\walletModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;


class WalletController extends Controller
{
    //
    public function createWalletAddress(Request $request)
    {
         try { 
            $validator = Validator::make($request->all(), [
                'wallet_address' => 'required',
                'wallet_name' => 'required',
                'wallet_qr' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 500);
            }
            if (!$request->hasFile('wallet_qr')) {
              return response()->json(['error' => "please upload wallet qr image"], 500);
            }
            $time_now = Carbon::now()->getTimestamp();
            $wallet_qr_image = $request->file('wallet_qr');
            $filename = strval($time_now) . '-' . $wallet_qr_image->getClientOriginalName();
            $new_wallet = walletModel::create([
                'wallet_name' => $request->wallet_name,
                'wallet_address' => $request->wallet_address,
                'wallet_qr' => $request->file('wallet_qr')->storeAs('walletImage', $filename),
            ]);
            return response()->json(['wallet' => $new_wallet]);
         } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
         }
    }
    public function getWalletAddresses()
    {
          try {
               $walletAdresses = walletModel::all();
               return response()->json(['wallets' => $walletAdresses]);
          } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
    public function setDefaultWalletAddress(Request $request)
    {
          try {
               $validator = Validator::make($request->all(), [
                   'wallet_id' => 'required'
               ]);
               if ($validator->fails()) {
                     return response()->json(['error' => $validator->errors()], 500);
               }
               $id = $request->wallet_id;
               // set the old wallet false
               walletModel::where('isCurrent', '=', true)->update([
                  'isCurrent' => false
               ]);
               $current_wallet = walletModel::find($id);
               $current_wallet->update([
                 'isCurrent' => true
               ]);
               return response()->json(['wallet' => $current_wallet]);
          }
          catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
    public function getDefaultWalletAddress()
    {
          try {
            $current_wallet = walletModel::where('isCurrent', '=', true)->get();
            return response()->json(['wallet' => $current_wallet]);
          } catch(Exception $e) {
            Log::info($e->getMessage());
            return response()->json(["error" => "something went wrong please try again"], 500);
          }
    }
}
