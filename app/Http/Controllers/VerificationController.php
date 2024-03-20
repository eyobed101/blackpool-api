<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class VerificationController extends Controller
{
     //
     public function uploadVerification(Request $request)
     {
          $validator = Validator::make($request->all(), [
               'first_name' => 'required',
               'last_name' => 'required',
               'date_of_birth' => 'required',
               'city' => 'required',
               'country' => 'required'
          ]);
          if ($validator->fails()) {
               return response()->json(['error' => $validator->errors()], 500);
          }
          $input = $request->all();
          $input["user_id"] = Auth::user()->id;
          // Log::info($input);
          $verification = Verification::create($input);
          return response()->json($verification, 200);
     }

     public function changeProfile(Request $request)
     {

          $user = auth()->user();

          $userId = $user->id;

          $validator = Validator::make($request->all(), [
               'first_name' => 'required',
               'last_name' => 'required',
               'date_of_birth' => 'required',
               'city' => 'required',
               'country' => 'required'
          ]);

          if ($validator->fails()) {
               return response()->json(['error' => $validator->errors()], 500);
          }

          $verification = Verification::where('user_id', $userId)->first();

          if (!$verification) {
               return response()->json(['error' => 'Verification details not found for this user.'], 404);
          }

          $input = $request->all();
          // Update only the fields that are present in the request
          $verification->fill($input);
          $verification->save();

          return response()->json($verification, 200);
     }


     public function fetchVerificationDetails()
     {
          $user = auth()->user();

          $userId = $user->id;
          $verification = Verification::where('user_id', $userId)->first();

          if (!$verification) {
               return response()->json(['error' => 'Verification details not found for this user.'], 404);
          }

          $userDetails = $verification->user;

          return response()->json([
               'verification_details' => $verification,
               'user_details' => $userDetails,
          ], 200);
     }
     public function adminVerifyUsers(Request $request)
     {
          try {
               if (Auth::user()->role == 'ADMIN' || Auth::user()->role == "SUPERADMIN") {
                    // lets get the unverified users
                    $unverified_users = Verification::where('isVerified', '=', false)->with('user')->get();
                    Log::info(strval($unverified_users));
                    return response()->json($unverified_users, 200);

               } else if (Auth::user()->role == 'USER') {
                    return response()->json(["error" => "unauthorized access"], 403);
               } else {
                    return response()->json(["error" => "unauthorized access"], 403);
               }

          } catch (Exception $e) {
               Log::info($e->getMessage());
          }
     }
     public function adminActivateUser(Request $request)
     {
          $validator = Validator::make($request->all(), [
               'user_id' => 'required',
          ]);
          if ($validator->fails()) {
               return response()->json(['error' => $validator->errors()], 500);
          }
          try {
               $user_id = $request->user_id;
               $user = User::find($user_id);
               if (is_null($user)) {
                    return response()->json(["error" => "user doesn't exist"], 404);
               }
               $user_verification = Verification::where('user_id', '=', $user->id)->get();
               $user_verification[0]->isVerified = true;
               $user_verification[0]->save();
               $user->verification_status = "VERIFIED";
               $user->save();
               Log::info("verified the user " . strval($user));
               return response()->json(["user" => $user], 200);
          } catch (Exception $e) {
               Log::info($e->getMessage());
               return response()->json(['error' => "something went wrong"], 500);
          }
     }
     public function adminDisableUser(Request $request)
     {
          $validator = Validator::make($request->all(), [
               'user_id' => 'required'
          ]);
          // check if request is valid
          if ($validator->fails()) {
               return response()->json(['error' => $validator]);
          }
          try {
               $user = User::find($request->user_id);
               if (is_null($user)) {
                    return response()->json(["error" => "user doesn't exist"], 404);
               }
               $user->verification_status = "DISABLED";
               $user->save();
               Log::info("disabled the user " . strval($user));
               return response()->json(["user" => $user]);
          } catch (Exception $e) {
               Log::error($e->getMessage());
               return response(["error" => "something went wrong please try again"]);
          }
     }

}

