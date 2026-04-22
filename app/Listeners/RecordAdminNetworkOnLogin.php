<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;
use App\Models\AllowedNetwork;

class RecordAdminNetworkOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user || ($user->role ?? '') !== 'admin') {
            return;
        }

        // Resolve request IP
        try {
            $ip = request()->ip();
        } catch (\Exception $e) {
            return;
        }

        if (!$ip) return;

        // Normalize to CIDR (/32 for IPv4, /128 for IPv6)
        $cidr = (strpos($ip, ':') !== false) ? ($ip . '/128') : ($ip . '/32');

        // Insert if not exists
        if (!AllowedNetwork::where('cidr', $cidr)->exists()) {
            AllowedNetwork::create(['cidr' => $cidr, 'label' => 'admin-login']);
        }

        // Clear cached copy
        Cache::forget('attendance.allowed_networks');
    }
}
