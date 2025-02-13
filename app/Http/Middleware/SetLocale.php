<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check for the 'Accept-Language' header in the request
        if ($locale = $request->header('Accept-Language')) {
            // Set the application locale to the provided value
            App::setLocale($locale);
        }

        return $next($request);
    }
}
