<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Closure;

class IsInstalled
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
    // Check if the .env file exists. If not, redirect to the installer.
    $envPath = base_path('.env');
    if (!file_exists($envPath)) {
        return redirect(url('/') . '/install');
    }

    // The license check has been removed. Always allow the request to continue.
    return $next($request);
}
}