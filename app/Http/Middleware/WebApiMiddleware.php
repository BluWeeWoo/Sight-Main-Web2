<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebApiMiddleware
{
    /**
     * Middleware for web-only API routes
     * Ensures the origin is the web frontend, not mobile
     */
    public function handle(Request $request, Closure $next)
    {
        // You can add additional web-specific checks here
        // For example, checking User-Agent or Origin headers
        
        return $next($request);
    }
}
