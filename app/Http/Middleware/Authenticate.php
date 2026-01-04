<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        // Check the request path to determine which login route to use
        if ($request->is('admin*')) {
            return route('admin.login');
        }
        
        // Default to admin login if no match
        return route('admin.login');
    }
}
