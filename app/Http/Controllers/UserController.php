<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        $users = User::request()->with('games')->paginate(request()->limit ?? 10);

        return response()->json([
            'success' => true,
            'data'    => [
                'users' => $users                
            ]
        ],$users->total() > 0 ? 200 : 404);
    }


    public function show($id){
        $user = User::with('games')->find($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'user'  => $user,
            ]
        ],$user ? 200 : 404);
    }
}
