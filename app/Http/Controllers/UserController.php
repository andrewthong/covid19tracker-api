<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Common;
use App\User;

class UserController extends Controller
{
    static function getUsers( Request $request )
    {
        return User::with('roles')->get();
    }

    /**
     * function to create editors
     */
    static function createUser( Request $request )
    {
        // default role
        $role = 'editor';

        // 
        if( !request('name') || !request('email') ) {
            abort(400, "Missing name / email");
        }

        //
        if( strlen( request('password') ) < 12 ) {
            abort(400, "Password should be at least 12 characters");
        }

        // quick validate email
        $user = User::where('email', '=', request('email'))->first();
        if ($user !== null) {
            abort(400, "This email address is already registered");
        }

        // base user object
        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make( request('password') )
        ]);

        $user->save();

        $user->assignRole($role);

        return response([
            'message' => "User created",
            'user' => $user,
        ], 200);
    }
}
