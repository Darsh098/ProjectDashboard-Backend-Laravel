<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before Sending Request Action
        if ($request->headers->get('token') !== 'my-secret-token') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $response = $next($request);

        // After Sending Request Action
        \Log::info('Response Status: ' . $response->status());
        \Log::info('Response Content: ' . $response->getContent());

        return $response;
    }
}
