<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $data['password'] = bcrypt($request->password);
        $data['wallet'] = 0;
        $data['role'] = $request->role;

        $user = User::create($data);

        $token = $user->createToken('API Token')->accessToken;

        return response([ 'user' => $user, 'token' => $token]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details. 
            Please try again']);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        return response(['user' => auth()->user(), 'token' => $token]);

    }

    public function logout(Request $request)
    {  
        try {
            if ($request->user()) {
                $request->user()->token()->revoke();
                return response()->json(['status'=>'success', 'message'=> 'User is logout.'],200);
            } 
            else{ 
                return response()->json(['status'=>'error', 'message'=> 'Unauthorised.'],Response::HTTP_UNAUTHORIZED);
            } 
        } catch (Exception $e) {
            Log::error('Failed to Logout due to  | ' .$e->getMessage());
        }
    } 

    //Add money to wallet
    public function addMoney(Request $request){
        try {
            if (Gate::allows('isUser')) {
                $validator = Validator::make($request->all(), [
                    'wallet' => ['required', 'numeric', 'min:3','max:100','regex:/^\d+(\.\d{1,2})?$/']
                ]);
                if ($validator->fails()) {
                    return response()->json(['status'=>'error', 'message'=> $validator->errors()], 400);
                }
                $id = $request->user()->id;
            
                $user = User::where('id',$id)->count();
                if($user >= 1){
                    $user = User::where('id',$id)->first();
                    $user->wallet += $request->wallet;
                    $user->save();
                    return response()->json(['status'=>'success', 'message'=> 'Money added successfully in your wallet.']);

                }else{
                    return response()->json(['status'=>'error', 'message'=> 'User not found'], 400);
                }
            }else{
                return response()->json(['status'=>'error', 'message'=> 'You don\'t have permission to access this.'], 400);

            }
                      
        
        } catch (Exception $e) {
            Log::error('Failed to add money due to  | ' .$e->getMessage());
        }

    }

    // Buy cookie
    public function buyCookie(Request $request){
        try {
                if (Gate::allows('isUser')) {
                $validator = Validator::make($request->all(), [
                    'cookie' => ['required', 'numeric', 'min:1','regex:/^\d+$/']
                ]);
                if ($validator->fails()) {
                    return response()->json(['status'=>'error', 'message'=> $validator->errors()], 400);
                }
            
                $id = $request->user()->id;
                $user = User::where('id',$id)->count();
                if($user >= 1){
                    $user = User::where('id',$id)->first();
                    $cookie = $request->cookie;
                    if($user->wallet >= $cookie){
                        $user->wallet -= $request->cookie;
                        $user->save();
                        return response()->json(['status'=>'success', 'message'=> 'Cookie buy successfully.'],200);
                    }else{
                        return response()->json(['status'=>'error', 'message'=> 'Your wallet is insufficient.'],400);

                    }
                
                }else{
                    return response()->json(['status'=>'error', 'message'=> 'User not found'], 400);

                }
            }else{
                return response()->json(['status'=>'error', 'message'=> 'You don\'t have permission to access this.'], 400);

            }
        
        } catch (Exception $e) {
            Log::error('Failed to buy a cookie due to  | ' .$e->getMessage());
        }

    }
}
