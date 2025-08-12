<?php

namespace App\Listeners;

use App\Services\LoginDurationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartLoginSession
{
    protected LoginDurationService $loginDurationService;

    public function __construct(LoginDurationService $loginDurationService)
    {
        $this->loginDurationService = $loginDurationService;
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Auth\Events\Login $event): void
    {
        $this->loginDurationService->createSession(
            $event->user->id,
            $event->guard ?? 'sanctum'
        );
    }
}
