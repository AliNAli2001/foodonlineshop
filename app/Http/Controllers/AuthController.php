<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth as AuthFacade;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

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

        return redirect()->route('verify-email')->with('success', 'Registration successful. Please verify your email.');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
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
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        AuthFacade::guard('client')->login($client);

        return redirect()->route('home')->with('success', 'Logged in successfully.');
    }

    /**
     * Handle client logout.
     */
    public function logout()
    {
        AuthFacade::guard('client')->logout();
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }

    /**
     * Show email verification form.
     */
    public function showVerifyEmail()
    {
        if (!AuthFacade::guard('client')->check()) {
            return redirect()->route('register');
        }

        return view('auth.verify-email');
    }

    /**
     * Verify email with code.
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:10',
        ]);

        $client = AuthFacade::guard('client')->user();
        $verificationCode = VerificationCode::where('client_id', $client->id)
            ->where('type', 'email')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode || $verificationCode->isExpired()) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $client->update(['email_verified' => true]);
        $verificationCode->delete();

        return redirect()->route('verify-phone')->with('success', 'Email verified successfully.');
    }

    /**
     * Show phone verification form.
     */
    public function showVerifyPhone()
    {
        if (!AuthFacade::guard('client')->check()) {
            return redirect()->route('register');
        }

        return view('auth.verify-phone');
    }

    /**
     * Verify phone with code.
     */
    public function verifyPhone(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:10',
        ]);

        $client = AuthFacade::guard('client')->user();
        $verificationCode = VerificationCode::where('client_id', $client->id)
            ->where('type', 'phone')
            ->where('code', $validated['code'])
            ->first();

        if (!$verificationCode || $verificationCode->isExpired()) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $client->update(['phone_verified' => true]);
        $verificationCode->delete();

        return redirect()->route('home')->with('success', 'Phone verified successfully.');
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

