<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect('login')->with('error', 'Please login to continue.');
        }

        $user = Auth::user();

        // If admin role, allow all
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Check if user has at least one of the required permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this resource. Please Contact IT Admin.');
    }
}