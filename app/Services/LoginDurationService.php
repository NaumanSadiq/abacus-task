<?php

namespace App\Services;

use App\Models\LoginSession;
use Illuminate\Database\Eloquent\Collection;

class LoginDurationService
{
    /**
     * Get total login duration for a user
     *
     * @param int $userId
     * @return array
     */
    public function getTotalDuration(int $userId): array
    {
        // Get completed sessions (already have duration calculated)
        $completedSessions = LoginSession::where('user_id', $userId)
            ->whereNotNull('duration_seconds')
            ->get();
        
        $totalSeconds = $completedSessions->sum('duration_seconds');
        
        // Get active session (current login) and calculate current duration
        $activeSession = LoginSession::where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->latest()
            ->first();
        
        if ($activeSession) {
            // Calculate current session duration
            $currentDuration = now()->diffInSeconds($activeSession->logged_in_at);
            $totalSeconds += $currentDuration;
        }
            
        $totalMinutes = round($totalSeconds / 60, 2);
        $totalHours = round($totalSeconds / 3600, 2);
        
        return [
            'total_seconds' => $totalSeconds,
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'formatted' => [
                'seconds' => $totalSeconds,
                'minutes' => $totalMinutes,
                'hours' => $totalHours
            ],
            'current_session_active' => $activeSession ? true : false,
            'current_session_duration' => $activeSession ? $currentDuration : 0
        ];
    }

    /**
     * Get login sessions for a user
     *
     * @param int $userId
     * @return array
     */
    public function getSessions(int $userId): array
    {
        $sessions = LoginSession::where('user_id', $userId)
            ->orderBy('logged_in_at', 'desc')
            ->get();

        $formattedSessions = $sessions->map(function ($session) {
            return $this->formatSession($session);
        });

        return [
            'sessions' => $formattedSessions,
            'total_sessions' => $sessions->count(),
            'active_sessions' => $sessions->where('status', 'active')->count(),
            'completed_sessions' => $sessions->where('status', 'completed')->count()
        ];
    }

    /**
     * Format a single login session
     *
     * @param LoginSession $session
     * @return array
     */
    protected function formatSession(LoginSession $session): array
    {
        $duration = $session->duration_seconds ?? 0;
        
        // If session is still active, calculate current duration
        if (!$session->logged_out_at) {
            $duration = now()->diffInSeconds($session->logged_in_at);
        }
        
        $durationMinutes = round($duration / 60, 2);
        $durationHours = round($duration / 3600, 2);
        
        return [
            'id' => $session->id,
            'logged_in_at' => $session->logged_in_at->format('Y-m-d H:i:s'),
            'logged_out_at' => $session->logged_out_at ? $session->logged_out_at->format('Y-m-d H:i:s') : null,
            'duration_seconds' => $duration,
            'duration_minutes' => $durationMinutes,
            'duration_hours' => $durationHours,
            'status' => $session->logged_out_at ? 'completed' : 'active',
            'auth_guard' => $session->auth_guard,
            'is_current_session' => !$session->logged_out_at
        ];
    }

    /**
     * Create a new login session
     *
     * @param int $userId
     * @param string $guard
     * @return LoginSession
     */
    public function createSession(int $userId, string $guard = 'sanctum'): LoginSession
    {
        return LoginSession::create([
            'user_id' => $userId,
            'logged_in_at' => now(),
            'auth_guard' => $guard,
        ]);
    }

    /**
     * End a login session and calculate duration
     *
     * @param int $userId
     * @return bool
     */
    public function endSession(int $userId): bool
    {
        $session = LoginSession::where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'logged_out_at' => now(),
                'duration_seconds' => now()->diffInSeconds($session->logged_in_at),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Get current active session duration for a user
     *
     * @param int $userId
     * @return array|null
     */
    public function getCurrentSessionDuration(int $userId): ?array
    {
        $session = LoginSession::where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->latest()
            ->first();

        if (!$session) {
            return null;
        }

        $duration = now()->diffInSeconds($session->logged_in_at);
        
        return [
            'session_id' => $session->id,
            'logged_in_at' => $session->logged_in_at->format('Y-m-d H:i:s'),
            'duration_seconds' => $duration,
            'duration_minutes' => round($duration / 60, 2),
            'duration_hours' => round($duration / 3600, 2),
            'formatted_duration' => $this->formatDuration($duration)
        ];
    }

    /**
     * Format duration in human-readable format
     *
     * @param int $seconds
     * @return string
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            $minutes = round($seconds / 60, 1);
            return "{$minutes} minutes";
        } else {
            $hours = round($seconds / 3600, 1);
            return "{$hours} hours";
        }
    }
} 