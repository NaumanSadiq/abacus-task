<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EndLoginSession
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
    public function handle(\Illuminate\Auth\Events\Logout $event): void {
        $session = \App\Models\LoginSession::where('user_id',$event->user->id)
            ->whereNull('logged_out_at')->latest()->first();
        if ($session) {
            $session->update([
                'logged_out_at'=>now(),
                'duration_seconds'=>now()->diffInSeconds($session->logged_in_at),
            ]);
        }
    }
}
