<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\LoginSession;
use Illuminate\Http\Request;

class LoginDurationController extends Controller
{
    public function total(Request $req): \Illuminate\Http\JsonResponse
    {
        $seconds = LoginSession::where('user_id', $req->user()->id)
            ->sum('duration_seconds');
        return response()->json(['total_seconds' => $seconds]);
    }
}
