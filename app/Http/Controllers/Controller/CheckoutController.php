<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    use ApiResponse;

    protected CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    /**
     * View checkout data without creating order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function viewCheckout(Request $request): JsonResponse
    {
        try {
            $cartItems = $request->input('items', []);
            $checkoutData = $this->checkoutService->viewCheckout($cartItems);
            
            return $this->successResponse($checkoutData, 'Checkout data retrieved successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to view checkout: ' . $e->getMessage());
        }
    }

    /**
     * Create a new order
     *
     * @param CheckoutRequest $request
     * @return JsonResponse
     */
    public function createOrder(CheckoutRequest $request): JsonResponse
    {
        try {
            $orderData = $request->validated();
            $result = $this->checkoutService->createOrder($orderData, $request->user()->id);
            
            return $this->createdResponse($result, 'Order created successfully');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Simulate payment processing
     *
     * @param Request $request
     * @param Order $order
     * @return JsonResponse
     */
    public function simulatePayment(Request $request, Order $order): JsonResponse
    {
        try {
            // Check if user owns the order
            if ($order->user_id !== $request->user()->id) {
                return $this->forbiddenResponse('You can only process your own orders');
            }

            $result = $this->checkoutService->simulatePayment($order->id, $request->user()->id);
            
            if ($result['success']) {
                return $this->successResponse($result['data'], $result['message']);
            } else {
                return $this->errorResponse($result['message'], 422, $result['data']);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Payment simulation failed: ' . $e->getMessage());
        }
    }
}
