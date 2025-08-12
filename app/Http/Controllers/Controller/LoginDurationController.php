<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\LoginSession;
use Illuminate\Http\Request;

class LoginDurationController extends Controller
{
    public function total(Request $req): \Illuminate\Http\JsonResponse
    {
        $totalSeconds = LoginSession::where('user_id', $req->user()->id)
            ->whereNotNull('duration_seconds')
            ->sum('duration_seconds');
            
        $totalMinutes = round($totalSeconds / 60, 2);
        $totalHours = round($totalSeconds / 3600, 2);
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_seconds' => $totalSeconds,
                'total_minutes' => $totalMinutes,
                'total_hours' => $totalHours,
                'formatted' => [
                    'seconds' => $totalSeconds,
                    'minutes' => $totalMinutes,
                    'hours' => $totalHours
                ]
            ]
        ]);
    }

    public function sessions(Request $req): \Illuminate\Http\JsonResponse
    {
        $sessions = LoginSession::where('user_id', $req->user()->id)
            ->orderBy('logged_in_at', 'desc')
            ->get()
            ->map(function ($session) {
                $duration = $session->duration_seconds ?? 0;
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
                    'auth_guard' => $session->auth_guard
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'sessions' => $sessions,
                'total_sessions' => $sessions->count(),
                'active_sessions' => $sessions->where('status', 'active')->count(),
                'completed_sessions' => $sessions->where('status', 'completed')->count()
            ]
        ]);
    }
}
