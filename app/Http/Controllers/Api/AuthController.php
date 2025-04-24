<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;

class AuthController extends Controller
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

    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:tbl_user,email',
                'password' => 'required|min:6',
                'user_name' => 'nullable|string|max:200',
                'first_name' => 'nullable|string|max:200',
                'last_name' => 'nullable|string|max:200'
            ]);
        } catch (ValidationException $e) {
            return $this->respond(0, 'Validation failed', $e->errors());
        }

        // Hash the password using md5
        $hashedPassword = md5($request->password);

        // Insert user
        $id = DB::table('tbl_user')->insertGetId([
            'email' => $request->email,
            'password' => $hashedPassword,
            'user_name' => $request->user_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'is_employee' => true,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Generate token using Str
        // $token = Str::random(60);

        // Generate JWT token
        $now = Carbon::now();
        // $expires = $now->copy()->addDays(7);
        $expires = $now->copy()->addMinutes(1); // For testing purposes, set expiration to 1 minute
        $payload = [
            'iss' => 'project-managment', // Issuer
            'uid' => $id, // user ID
            'iat' => $now->timestamp, // Issued at
            'exp' => $expires->timestamp // Expiration
        ];
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        DB::insert(
            'INSERT INTO tbl_session (user_id, token, created_at, expired_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)',
            [$id, $token, $now->toDateTimeString(), $expires->toDateTimeString(), $ip, $userAgent]
        );

        return $this->respond(1, 'Signup successful', ['token' => $token]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
        } catch (ValidationException $e) {
            return $this->respond(0, 'Validation failed', $e->errors());
        }

        // Step 1: Check if user exists
        $user = DB::selectOne(
            'SELECT * FROM tbl_user WHERE email = ? LIMIT 1',
            [$request->email]
        );

        if (!$user) {
            return $this->respond(0, 'User not found');
        }

        // Step 2: Compare MD5 password
        if ($user->password !== md5($request->password)) {
            return $this->respond(0, 'Invalid password');
        }

        // Step 3: Generate token
        // $token = Str::random(60);

        // Step 3: Generate JWT token
        $now = Carbon::now();
        // $expires = $now->copy()->addDays(7);
        $expires = $now->copy()->addMinutes(1); // For testing purposes, set expiration to 1 minute
        $payload = [
            'iss' => 'project-managment', // Issuer
            'uid' => $user->id, // user ID
            'iat' => $now->timestamp, // Issued at
            'exp' => $expires->timestamp // Expiration
        ];
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');

        // Step 4: Store token in tbl_session
        DB::insert(
            'INSERT INTO tbl_session (user_id, token, created_at, expired_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)',
            [$user->id, $token, $now->toDateTimeString(), $expires->toDateTimeString(), $ip, $userAgent]
        );

        // Step 5: Return token
        return $this->respond(1, 'Login successful', ['token' => $token, "id" => $user->id, "first_name" => $user->first_name, "last_name" => $user->last_name]);
    }

    public function verify(Request $request)
    {

        $token = $request->headers->get('token');
        if (!$token) {
            return $this->respond(0, 'Token is required');
        }

        try {
            // $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            // return response()->json(['status' => 1, 'message' => 'Token is valid', 'data' => $decoded], 200);

            $session = DB::selectOne(
                'SELECT * FROM tbl_session WHERE token = ? LIMIT 1',
                [$token]
            );

            if (!$session) {
                return $this->respond(0, 'Session Expired');
            } else if ($session->expired_at < now()) {
                return $this->respond(0, 'Token is expired');
            }
            return $this->respond(1, 'Token is valid');
        } catch (\Exception $e) {
            return $this->respond(0, 'Token is invalid', ['error' => $e->getMessage()]);
        }
    }
}
