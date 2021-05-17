<?php

namespace App\Http\Middleware;

use App\Data\Constants;
use App\Data\StatusCode;
use Closure;
use Auth;

class IsAdmin
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
        if (Auth::user() &&  Auth::user()->hasRole([Constants::ROLE_KEY_SUPER_ADMIN, Constants::ROLE_KEY_ADMIN, Constants::ROLE_KEY_STAFF])) {
            return $next($request);
        }

        return response()->json([
            'message'=> 'You have no permission for this action'
        ], StatusCode::HTTP_FORBIDDEN);
    }
}
