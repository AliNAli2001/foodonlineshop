<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Get client profile.
     */
    public function profile()
    {
        $clientId = auth('client')->id();
        $client = Client::find($clientId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'email_verified' => $client->email_verified,
                'phone_verified' => $client->phone_verified,
                'address_details' => $client->address_details,
                'language_preference' => $client->language_preference,
                'promo_consent' => $client->promo_consent,
                'created_at' => $client->created_at,
            ],
        ], 200);
    }

    /**
     * Update client profile.
     */
    public function updateProfile(Request $request)
    {
        $clientId = auth('client')->id();
        $client = Client::find($clientId);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20|unique:clients,phone,' . $clientId,
            'address_details' => 'sometimes|string|max:500',
            'language_preference' => 'sometimes|in:ar,en',
            'promo_consent' => 'sometimes|boolean',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $client->id,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'email_verified' => $client->email_verified,
                'phone_verified' => $client->phone_verified,
                'address_details' => $client->address_details,
                'language_preference' => $client->language_preference,
                'promo_consent' => $client->promo_consent,
            ],
        ], 200);
    }
}

