<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ViewerReadOnly
{
    public function handle(Request $request, Closure $next)
    {
        // Viewer role is blocked from all mutating operations
        if (auth('api')->check() && auth('api')->user()->role === 'viewer') {
            if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
                return response()->json([
                    'error' => 'Access denied. Your account has view-only permissions.'
                ], 403);
            }
        }

        return $next($request);
    }
}
