<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request) {
      $validateUser = Validator::make(
        $request->all(),
        [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]
      );
      if ($validateUser->fails()) {
          return response()->json([
              'status' => false,
              'message' => 'Validation Error',
              'errors' => $validateUser->errors()->all(),
          ], 401);
      }
      $user= User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' =>$request->password,
      ]);
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'user' => $user,
        ], 200);
    }
     public function login(Request $request) {
        $validateUser = Validator::make(
        $request->all(),
        [
            'email' => 'required|email',
            'password' => 'required',
        ]
      );
        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Authentication Error',
                'errors' => $validateUser->errors()->all(),
            ], 404);
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials',
                'errors' =>$validateUser->errors()->all(),
            ], 401);
        }
    }
     public function logout(Request $request) {
        
    }
}
