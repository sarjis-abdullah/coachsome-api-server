<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;

/**
 * Class Localization
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class AssignFrontendVersion
{

    /**
     * Localization constructor.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('fe-version', 'v11');

        return $response;
    }
}

