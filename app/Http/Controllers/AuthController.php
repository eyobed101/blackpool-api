<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // lets create the user first
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'phone_number' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['name'] =  $user->name;
        return response()->json(['success'=>$success], 200);

    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response()->json($response, 200);
            } else {
                $response = ["message" => "wrong username or password"];
                return response()->json($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }
    }
    public function superAdminCreateAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'phone_number' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['role'] = 'ADMIN';
        $user = User::create($input);
        $success['admin'] =  $user;
        return response()->json(['success'=>$success], 200);
    }
    public function createUserWithAdminId(Request $request)
    {
        $admin_id = $request->route('admin_id');
        try {
            $admin = User::findOrFail($admin_id);
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'phone_number' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 401);
            }
            if($admin->role != "ADMIN")
            {
                  return response()->json(['error' => 'user not registered agent'], 500);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['admin_id'] = $admin->id;
            $user = User::create($input);
            $success['admin'] =  $user;
            return response()->json(['success'=>$success], 200);
        } catch(Exception $e) {
            Log::info($e->getMessage());
           return response()->json(['error' => 'Something went wrong']);
        }
    }
    public function showAllAdmins()
    {
          // we are going to use this routes to show all the available agents
          try {
             $admin_list = User::where('role', '=', 'ADMIN')->get();
             foreach($admin_list as $agent) {
                // lets get all the users with the agent id in the registration
                $customers = User::where('admin_id', '=', $agent->id)->with('transaction')->get();
                $agent["customer"] = $customers;
           }
             return response()->json(['admins'  => $admin_list]);
          } catch(Exception $e) {
              return response()->json(['error' => 'something went wrong']);
          }

    }
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], 200);
    }

    public function GetAllUsers()
    {
          try {
             $users = User::with('transaction')->where('role', '=', 'USER')->where(function ($query){
                        return $query->where('verification_status', '=', 'VERIFIED')->whereOr('verification_status', '=', 'DISABLED');
             })->get();
             $pending_users = User::where('verification_status', '=', 'ONBOARDING')->where('role', '=', 'USER')->get();
             $verified_users = User::where('verification_status', '=', 'VERIFIED')->where('role', '=', 'USER')->get();
             $disabled_users = User::where('verification_status', '=', 'DISABLED')->where('role', '=', 'USER')->get();
             return response()->json(["pending_users" => count($pending_users), "verified_users" => count($verified_users), "disabled_users" => count($disabled_users), "customers" => $users]);
          } catch (Exception $e)
          {
               Log::error($e->getMessage());
               return response()->json(["error" => "something went wrong"]);
          }
    }
    // lets get the latest admins by sorting of 
    public function GetLatestAdmins()
    {
         try {
             $agents = User::where('role', '=', 'ADMIN')->orderBy('created_at', 'desc')->with('admin')->take(5)->get();
             return response()->json(['data' => $agents]);
             
         } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(["error" => "something went wrong"]);
         }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
