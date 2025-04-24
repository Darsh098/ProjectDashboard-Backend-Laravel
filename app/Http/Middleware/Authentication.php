<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authentication
{
    private $jwtSecret;

    public function __construct()
    {
        $this->jwtSecret = env('JWT_SECRET');
    }

    // Helper method for standardized responses
    private function respond($status, $message, $data = null)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->headers->get('token');
        if (!$token) {
            return $this->respond(0, 'Token is required');
        }
        try {
            JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $next($request);
        } catch (\Exception $e) {
            return $this->respond(0, 'Token is invalid', ['error' => $e->getMessage()]);
        }

    }
}
