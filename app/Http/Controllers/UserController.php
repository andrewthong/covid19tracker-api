<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Common;
use App\User;

class UserController extends Controller
{
    /**
     * get all users
     */
    static function getUsers( Request $request )
    {
        return User::with('roles', 'provinces')->get();
    }

    /**
     * helper to retrieve a user w/ role and province
     */
    static function getUser( $id )
    {
        $user = User::with('roles', 'provinces')->find($id);
        //
        if( !$user ) {
            return abort(400, "User does not exist");
        }
        // block admin editing
        if( $user->hasRole('admin') ) {
            return abort(400, "Admins cannot be modified here");
        }
        return $user;
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
            'password' => Hash::make( request('password') ),
        ]);

        $user->save();

        // sync province assignments (permissions)
        $user->provinces()->sync( request('provinces') );

        // role
        $user->assignRole($role);

        return response([
            'message' => "User created",
            'user' => $user,
        ], 200);
    }

    /**
     * function to update editors
     */
    static function updateUser( $id, Request $request )
    {
        $user = User::find($id);

        // check if user
        if( !$user ) {
            abort(400, "Invalid user");
        }

        // check details
        if( !request('name') || !request('email') ) {
            abort(400, "Missing name / email");
        }

        //
        if( request('password') && strlen( request('password') ) < 12 ) {
            abort(400, "Password should be at least 12 characters");
        }

        // update user details
        $user->name = request('name');
        $user->email = request('email');
        $password_updated = false;
        if( request('password') ) {
            $user->password = Hash::make( request('password') );
            $password_updated = true;
        }
        // save
        $user->save();

        // sync province assignments (permissions)
        $user->provinces()->sync( request('provinces') );

        return response([
            'message' => "User ${id} updated",
            'passwordUpdated' => $password_updated,
        ], 200);

    }
}
