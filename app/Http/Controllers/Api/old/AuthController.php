<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth as AuthFacade;

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

        AuthFacade::guard('client')->login($client);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email and phone.',
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
        ]);

        $client = Client::where('email', $validated['email'])->first();

        if (!$client || !Hash::check($validated['password'], $client->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        AuthFacade::guard('client')->login($client);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'client' => $client,
        ], 200);
    }

    /**
     * Handle client logout.
     */
    public function logout()
    {
        AuthFacade::guard('client')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }

    /**
     * Verify email with code.
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $clientId = auth('client')->id();
        $verificationCode = VerificationCode::where('client_id', $clientId)
            ->where('type', 'email')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        $client = Client::find($clientId);
        $client->update(['email_verified' => true]);
        $verificationCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
        ], 200);
    }

    /**
     * Verify phone with code.
     */
    public function verifyPhone(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $clientId = auth('client')->id();
        $verificationCode = VerificationCode::where('client_id', $clientId)
            ->where('type', 'phone')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.',
            ], 400);
        }

        $client = Client::find($clientId);
        $client->update(['phone_verified' => true]);
        $verificationCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Phone verified successfully.',
        ], 200);
    }

    /**
     * Generate verification code.
     */
    private function generateVerificationCode($client, $type)
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'client_id' => $client->id,
            'type' => $type,
            'code' => $code,
        ]);

        // TODO: Send code via email or SMS
    }
}

