<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowed_origins = explode(',', env('MANAGE_URL', 'http://localhost:3000'));
        $request_origin = $request->headers->get('origin');

        if (in_array($request_origin, $allowed_origins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $request_origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $next($request);
    }
}
