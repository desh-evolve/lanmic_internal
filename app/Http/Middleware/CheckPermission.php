<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    // public function handle(Request $request, Closure $next, $permission): Response
    // {
    //     if (!Auth::check()) {
    //         return redirect('login');
    //     }

    //     $user = Auth::user();

    //     if ($user->hasPermission($permission)) {
    //         return $next($request);
    //     }

    //     abort(403, 'Unauthorized action.');
    // }

    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action. You do not have the required permission.');
    }
}