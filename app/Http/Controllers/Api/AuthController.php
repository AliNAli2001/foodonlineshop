<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Client;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle client registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'password' => 'required|string|min:8|confirmed',
            'language_preference' => 'required|in:ar,en',
        ]);

        $client = Client::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'language_preference' => $validated['language_preference'],
        ]);

        // Generate verification codes
        $this->generateVerificationCode($client, 'email');
        $this->generateVerificationCode($client, 'phone');

        // Issue API token
        $token = $client->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email and phone.',
            'token' => $token,
            'client' => $client,
        ], 201);
    }

    /**
     * Handle client login.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string', // Optional, but useful for token name
        ]);

        $client = Client::where('email', $validated['email'])->first();

        if (!$client || !Hash::check($validated['password'], $client->password_hash)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($client->suspended_at) {
            return response()->json(['message' => 'Account is suspended. Please contact support.'], 403);
        }

        // Issue API token
        $token = $client->createToken($validated['device_name'])->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $token,
            'client' => $client,
        ]);
    }

    /**
     * Handle client logout.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Verify email with code.
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:10',
        ]);

        $client = $request->user();
        $verificationCode = VerificationCode::where('client_id', $client->id)
            ->where('type', 'email')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode || $verificationCode->isExpired()) {
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        $client->update(['email_verified' => true]);
        $verificationCode->delete();

        return response()->json(['message' => 'Email verified successfully.']);
    }

    /**
     * Verify phone with code.
     */
    public function verifyPhone(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:10',
        ]);

        $client = $request->user();
        $verificationCode = VerificationCode::where('client_id', $client->id)
            ->where('type', 'phone')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode || $verificationCode->isExpired()) {
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        $client->update(['phone_verified' => true]);
        $verificationCode->delete();

        return response()->json(['message' => 'Phone verified successfully.']);
    }

    /**
     * Generate a verification code.
     */
    private function generateVerificationCode(Client $client, string $type): void
    {
        $code = str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'client_id' => $client->id,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addHours(24),
        ]);
    }
} 
