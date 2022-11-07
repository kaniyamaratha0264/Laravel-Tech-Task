<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    //Add money to wallet
    public function addMoney(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'wallet' => ['required', 'numeric', 'min:3','max:100','regex:/^\d+(\.\d{1,2})?$/']
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>'error', 'message'=> $validator->errors()], 400);
            }
           
            $user = User::count();
            if($user >= 1){
                $user = User::first();
                $user->wallet += $request->wallet;
                $user->save();

            }else{
                $user = new User;
                $user->wallet = $request->wallet;
                $user->save();
            }
            return response()->json(['status'=>'success', 'message'=> 'Money added successfully in your wallet.']);            
        
        } catch (Exception $e) {
            Log::error('Failed to add money due to  | ' .$e->getMessage());
        }

    }
    
    // Buy cookie
    public function buyCookie(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'cookie' => ['required', 'numeric', 'min:1','regex:/^\d+$/']
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>'error', 'message'=> $validator->errors()], 400);
            }
           
            $user = User::count();
            if($user >= 1){
                $user = User::first();
                $cookie = $request->cookie;
                if($user->wallet >= $cookie){
                    $user->wallet -= $request->cookie;
                    $user->save();
                    return response()->json(['status'=>'success', 'message'=> 'Cookie buy successfully.'],200);
                }else{
                    return response()->json(['status'=>'error', 'message'=> 'Your wallet is insufficient.'],400);

                }
               
            }
            return response()->json(['status'=>'success', 'message'=> 'Money added successfully in your wallet.']);            
        
        } catch (Exception $e) {
            Log::error('Failed to buy a cookie due to  | ' .$e->getMessage());
        }

    }
}
