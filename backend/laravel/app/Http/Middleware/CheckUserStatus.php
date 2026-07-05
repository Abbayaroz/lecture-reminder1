<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is ' . $user->status . '. Contact administrator.'
                ], 403);
            }
        }

        return $next($request);
    }
}
