<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Services\LoginDurationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginDurationController extends Controller
{
    use ApiResponse;

    protected LoginDurationService $loginDurationService;

    public function __construct(LoginDurationService $loginDurationService)
    {
        $this->loginDurationService = $loginDurationService;
    }

    /**
     * Get total login duration for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function total(Request $request): JsonResponse
    {
        try {
            $durationData = $this->loginDurationService->getTotalDuration($request->user()->id);
            
            return $this->successResponse($durationData, 'Login duration retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve login duration: ' . $e->getMessage());
        }
    }

    /**
     * Get login sessions for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sessions(Request $request): JsonResponse
    {
        try {
            $sessionsData = $this->loginDurationService->getSessions($request->user()->id);
            
            return $this->successResponse($sessionsData, 'Login sessions retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve login sessions: ' . $e->getMessage());
        }
    }
}
