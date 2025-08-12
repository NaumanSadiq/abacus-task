<?php

namespace App\Listeners;

use App\Services\LoginDurationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EndLoginSession
{
    protected LoginDurationService $loginDurationService;

    public function __construct(LoginDurationService $loginDurationService)
    {
        $this->loginDurationService = $loginDurationService;
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Auth\Events\Logout $event): void
    {
        $this->loginDurationService->endSession($event->user->id);
    }
}
