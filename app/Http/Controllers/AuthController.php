<?php

namespace App\Http\Controllers;

use App\Utilities\ProxyRequest;
use Illuminate\Http\Request;

use App\Common;
use App\User;
use App\Province;
use App\HealthRegion;
use App\HrReport;
use App\Report;

class AuthController extends Controller
{
    protected $proxy;

    public function __construct(ProxyRequest $proxy)
    {
        $this->proxy = $proxy;
    }

    public function register()
    {
        $this->validate(request(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password')),
        ]);

        $resp = $this->proxy->grantPasswordToken(
            $user->email,
            request('password')
        );

        return response([
            'token' => $resp->access_token,
            'expiresIn' => $resp->expires_in,
            'message' => 'Your account has been created',
        ], 201);
    }

    public function login()
    {
        $user = User::where('email', request('email'))->first();

        abort_unless($user, 404, 'This combination does not exists.');
        abort_unless(
            \Hash::check(request('password'), $user->password),
            403,
            'This combination does not exists.'
        );

        $resp = $this->proxy
            ->grantPasswordToken(request('email'), request('password'));

        return response([
            'token' => $resp->access_token,
            'expiresIn' => $resp->expires_in,
            'message' => 'You have been logged in',
        ], 200);
    }

    public function refreshToken()
    {
        $resp = $this->proxy->refreshAccessToken();

        return response([
            'token' => $resp->access_token,
            'expiresIn' => $resp->expires_in,
            'message' => 'Token has been refreshed.',
        ], 200);
    }

    public function logout()
    {
        $token = request()->user()->token();
        $token->delete();

        // remove the httponly cookie
        cookie()->queue(cookie()->forget('refresh_token'));

        return response([
            'message' => 'You have been successfully logged out',
        ], 200);
    }

    public function test() {
        $token = request()->user()->token();
        return "HEY";
    }

    public function getReport( Request $request, $province ) {
        $provinces = Common::getProvinceCodes();
        $date = $request->date;
        // ensure valid date
        if( !Common::isValidDate( $date ) ) {
            return response([
            'message' => "Invalid date ({$date}) selected",
            ], 400);
        }
        // ensure valid province
        if( in_array( $province, $provinces ) ) {
            $response = [];
            $response['report'] = Report::firstOrNew([
                'province' => $province,
                'date' => $date
            ]);
            $regions = HealthRegion::where(['province' => $province]);
            $hr_uids = $regions->pluck('hr_uid')->toArray();
            $response['regions'] = $regions->get();
            $response['hr_reports'] = HrReport::whereIn('hr_uid', $hr_uids)->where([
                'date' => $date
            ]);
            return $response;
        }
        return response([
            'message' => "Invalid province ({$province}) selected",
        ], 400);
    }

}
