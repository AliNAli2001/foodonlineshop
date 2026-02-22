<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * List clients with simple search.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $clients = Client::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin.clients.index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show one client and their recent orders.
     */
    public function show(Client $client)
    {
        $client->loadCount('orders');

        $orders = $client->orders()
            ->select([
                'id',
                'status',
                'total_amount',
                'order_date',
                'created_at',
            ])
            ->latest('id')
            ->paginate(10);

        return Inertia::render('admin.clients.show', [
            'client' => $client,
            'orders' => $orders,
        ]);
    }

    /**
     * Reset client password and return generated value in flash once.
     */
    public function resetPassword(Client $client)
    {
        $newPassword = Str::password(12);

        $client->update([
            'password_hash' => Hash::make($newPassword),
        ]);

        $client->tokens()->delete();

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', "Client password reset successfully. New password: {$newPassword}");
    }

    /**
     * Suspend account and revoke API tokens.
     */
    public function suspend(Client $client)
    {
        if ($client->suspended_at) {
            return redirect()
                ->route('admin.clients.show', $client)
                ->with('error', 'Client account is already suspended.');
        }

        $client->update([
            'suspended_at' => now(),
        ]);

        $client->tokens()->delete();

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client account suspended successfully.');
    }

    /**
     * Reactivate suspended account.
     */
    public function activate(Client $client)
    {
        if (!$client->suspended_at) {
            return redirect()
                ->route('admin.clients.show', $client)
                ->with('error', 'Client account is already active.');
        }

        $client->update([
            'suspended_at' => null,
        ]);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Client account activated successfully.');
    }

    /**
     * Delete client account.
     */
    public function destroy(Client $client)
    {
        $client->tokens()->delete();
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
