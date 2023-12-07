<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
            $user_list = User::with('bet')->where('admin_id', '=', $admin_id)->get();
            return response()->json(['users' => $user_list], 200);
         } catch(Exception $e) {
              return response()->json(['error' => 'something went wrong'], 500);
         }
    }
    public function GetUsersDeposit()
    {
        try {
             $admin_id = Auth::user()->id;
             $user_list  = User::with('transaction')->where('admin_id', '=', $admin_id)->whereHas('transaction', function($query){
                     $query->where("status", "=", "success");
                     $query->where("type", '=', "DEPOSIT");
             })->get();
             return response()->json(["users" => $user_list], 200);
        } catch(Exception $e) {
             return response()->json(["error" => "Something went wrong please try again"]);
        }
    }
    
}
