<?php


namespace App\Http\Middleware;
use Closure;
use Illuminate\Foundation\Application;

/**
 * Class Localization
 *
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
class Localization
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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->header('Language-Code') ?? $this->app->config->get('app.locale');
        $this->app->setLocale($locale);
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }
}

