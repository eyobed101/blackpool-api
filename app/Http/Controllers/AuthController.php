<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailable;
use Carbon\Carbon;

use Exception;
use App\Mail\SendMail;

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
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // Check if the email already exists
        $existingUser = User::where('email', $request->input('email'))->first();
        if ($existingUser) {
            return response()->json(['error' => 'Email already exists.'], 409);
        }

        // Check if the phone number is null
        if ($request->input('phone_number') === null) {
            return response()->json(['error' => 'Please provide a phone number.'], 400);
        }

        // Create the user
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        // Prepare success response
        $success['name'] = $user->name;

        // Return success response
        return response()->json(['success' => $success], 200);
    }


    public function delete(Request $request)
    {
        $userId = auth()->id();


        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['success' => 'User deleted successfully'], 200);
    }

    public function changePassword(Request $request)
    {
        $userId = auth()->id();

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        return response()->json(['success' => 'Password changed successfully'], 200);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if($user->role == "USER")
                {
                        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                        $response = ['username' => $user->name, 'token' => $token];
                        return response()->json($response, 200);
                }
                else if($user->role == "ADMIN" || $user->role == "SUPERADMIN") {
                      // $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                      $response = ['username' => $user->name, 'email' => $user->email];
                      return response()->json($response, 200);
                }
            } else {
                $response = ["message" => "wrong username or password"];
                return response()->json($response, 422);
            }
        } else {
            $response = ["message" => 'User does not exist'];
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
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['role'] = 'ADMIN';
        $user = User::create($input);
        $success['admin'] = $user;
        return response()->json(['success' => $success], 200);
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
                return response()->json(['error' => $validator->errors()], 401);
            }
            if ($admin->role != "ADMIN") {
                return response()->json(['error' => 'user not registered agent'], 500);
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['admin_id'] = $admin->id;
            $user = User::create($input);
            $success['admin'] = $user;
            return response()->json(['success' => $success], 200);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json(['error' => 'Something went wrong']);
        }
    }
    public function showAllAdmins(Request $request)
    {
        // we are going to use this routes to show all the available agents
        try {
            $admin_list = User::where('role', '=', 'ADMIN')->get();
            $start_date =  $request->query('start', null);
            $end_date = $request->query('end', null);
            foreach ($admin_list as $agent) {
                // lets get all the users with the agent id in the registration
                $total_agent_users_withdrawal = 0;
                $total_agent_users_deposit = 0;
                $users_withdrawal = [];
                $users_deposit = [];
                $customers = User::where('admin_id', '=', $agent->id)->with('transaction')->get();
                if($start_date != null && $end_date != null)
                {
                    $users_withdrawal = User::where('admin_id', '=', $agent->id)->with(['transaction' => function ($query) use ($start_date, $end_date) {
                        $query->where('type', '=', 'WITHDRAW');
                        $query->whereBetween('created_at', [Carbon::parse($start_date), Carbon::parse($end_date)]);
                    }])->get();
                    $users_deposit = User::where('admin_id', '=', $agent->id)->with(['transaction' => function ($query) use ($start_date, $end_date) {
                        $query->where('type', '=', 'DEPOSIT');
                        $query->whereBetween('created_at', [Carbon::parse($start_date), Carbon::parse($end_date)]);
                    }])->get();
                } else {
                    $users_withdrawal = User::where('admin_id', '=', $agent->id)->with(['transaction' => function ($query) use ($start_date, $end_date) {
                        $query->where('type', '=', 'WITHDRAW');
                    }])->get();
                    $users_deposit = User::where('admin_id', '=', $agent->id)->with(['transaction' => function ($query) use ($start_date, $end_date) {
                        $query->where('type', '=', 'DEPOSIT');
                    }])->get();
                }
                foreach($users_withdrawal as $users) {
                     foreach($users->transaction as $withdraw) {
                          $total_agent_users_withdrawal = $total_agent_users_withdrawal + $withdraw->amount;
                     }
                }
                foreach($users_deposit as $users) {
                    foreach($users->transaction as $deposit) {
                         $total_agent_users_deposit = $total_agent_users_withdrawal + $deposit->amount;
                    }
               }
                $agent["customer"] = $customers;
                $agent["total_withdrawal"] = $total_agent_users_withdrawal;
                $agent["total_deposit"] = $total_agent_users_deposit;
                // Log::info($agent["total_withdrawal"]);
            }
            return response()->json(['admins' => $admin_list]);
        } catch (Exception $e) {
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
            $users = User::with('transaction')->where('role', '=', 'USER')->where(function ($query) {
                return $query->where('verification_status', '=', 'VERIFIED')->whereOr('verification_status', '=', 'DISABLED');
            })->get();
            $pending_users = User::where('verification_status', '=', 'ONBOARDING')->where('role', '=', 'USER')->get();
            $verified_users = User::all();
            $disabled_users = User::where('verification_status', '=', 'DISABLED')->where('role', '=', 'USER')->get();
            return response()->json(["pending_users" => count($pending_users), "verified_users" => count($verified_users), "disabled_users" => count($disabled_users), "customers" => $users]);
        } catch (Exception $e) {
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
    public function SendOTP(Request $request)
    {
        $otp = rand(1000,9999);
        Log::info("otp = ".$otp);
        $user = User::where('email','=', $request->email)->update(['otp'=>$otp]);
        if($user){
      //  send otp in the email
        $mail_details = [
            'subject' => 'Testing Application OTP',
            'body' => 'Your OTP is : '. $otp
        ];
        $testMailData = [
            'title' => 'Blackpool login OTP',
            'subject' => 'OTP for Login',
            'body' => 'Your OTP is : '. $otp
        ];

            Mail::to($request->email)->send(new SendMail($testMailData));

            return response(["status" => 200, "message" => "OTP sent successfully"]);
        } else {
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }
    public function verifyOtp(Request $request)
    {

        $user = User::where([['email', '=', $request->email], ['otp', '=', $request->otp]])->first();
        if ($user) {
            auth()->login($user, true);
            User::where('email', '=', $request->email)->update(['otp' => null]);
            $accessToken = auth()->user()->createToken('authToken')->accessToken;

            return response(["status" => 200, "message" => "Success", 'user' => auth()->user(), 'access_token' => $accessToken]);
        } else {
            return response(["status" => 401, 'message' => 'Invalid']);
        }
    }
    // lets add user bonus to the user
    public function AddBonusToUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        // lets then add the bonus to the user
        try {
            $selected_users = User::findOrFail($request->user_id);
            $updated_value = $selected_users->balance + $request->amount;
            $selected_users->update([
                 "balance" => $updated_value
            ]);
            $testMailData = [
                'title' => 'You have received a bonus !!!!!!!!',
                'subject' => 'Bonus Received',
                'body' => 'Dear ' . $selected_users->name . ' you have received ' . $request->amount . 'USDT.'
            ];
            Mail::to($selected_users->email)->send(new SendMail($testMailData));
            return response()->json(['message' => 'successfuly gave bonus'], 200);
       
        } catch(Exception $e) {
             return response()->json(['message' => 'something went wrong'], 500);
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
