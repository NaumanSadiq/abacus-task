<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartLoginSession
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Auth\Events\Login $event): void
    {
        \App\Models\LoginSession::create([
            'user_id' => $event->user->id,
            'logged_in_at' => now(),
            'auth_guard' => $event->guard ?? 'sanctum',
        ]);
    }
}
