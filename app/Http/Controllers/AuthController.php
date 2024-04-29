<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'data'    => [
                    'message' => 'Invalid login details'
                ]
            ], 401);
        }

        $user = auth()->user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' =>[
                'access_token'     => $token,
                'token_type'       => 'Bearer',
            ]
        ],);
    }


    public function register(Request $request){

        $validate = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                $validate->errors()
            ],422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            $token = $user->createToken('auth_token')->plainTextToken;
            
            DB::commit();
            return response()->json([
                'success' => true,
                'data' =>[
                    'access_token'     => $token,
                    'token_type'       => 'Bearer',
                ]
            ],200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ],500);
        }
    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'data'    => [
                'message' => 'Logout successfully'
            ]
        ],200);
    }


    public function refresh(){
        $user = auth()->user()->tokens()->delete();
        $token = auth()->user()->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success' => true,
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'Bearer',
            ]
        ],200);
    }


    public function me(){
        return response()->json([
            'success' => true,
            'data'    => [
                'user' => auth()->user(),
                'game' => User::find(auth()->user()->id)->games
            ]
        ],200);
    }

}
