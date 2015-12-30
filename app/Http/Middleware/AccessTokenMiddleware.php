<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class AccessTokenMiddleware
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
        $user_ac_token = $request->header('utoken');
        $user = User::where('auth_token', '=', $user_ac_token)->firstOrFail();
        $request->attributes->add(['CurrentUser' => $user]);
        return $next($request);
    }
}
