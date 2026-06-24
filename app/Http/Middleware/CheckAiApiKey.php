<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAiApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');

        if (!$key || $key !== config('services.ai.key')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
