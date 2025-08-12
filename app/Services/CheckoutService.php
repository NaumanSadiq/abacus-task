<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * View checkout data without creating order
     *
     * @param array $cartItems
     * @return array
     * @throws ValidationException
     */
    public function viewCheckout(array $cartItems): array
    {
        if (empty($cartItems)) {
            throw new ValidationException(null, 'No items in cart');
        }

        $checkoutData = [];
        $subtotal = 0;
        $errors = [];

        foreach ($cartItems as $item) {
            $product = $this->productService->getProduct($item['product_id'] ?? null);
            
            if (!$product) {
                $errors[] = "Product with ID {$item['product_id']} not found";
                continue;
            }

            $quantity = $item['quantity'] ?? 1;
            
            if (!$this->productService->hasSufficientStock($product->id, $quantity)) {
                $errors[] = "Insufficient stock for {$product->name}. Available: {$product->stock}";
                continue;
            }

            $lineTotal = $product->price_cents * $quantity;
            $subtotal += $lineTotal;

            $checkoutData[] = [
                'product' => $this->productService->formatProduct($product),
                'quantity' => $quantity,
                'line_total_cents' => $lineTotal,
                'line_total_formatted' => '$' . number_format($lineTotal / 100, 2)
            ];
        }

        if (!empty($errors)) {
            throw new ValidationException(null, implode(', ', $errors));
        }

        $tax = $this->calculateTax($subtotal);
        $total = $subtotal + $tax;

        return [
            'items' => $checkoutData,
            'subtotal_cents' => $subtotal,
            'subtotal_formatted' => '$' . number_format($subtotal / 100, 2),
            'tax_cents' => $tax,
            'tax_formatted' => '$' . number_format($tax / 100, 2),
            'total_cents' => $total,
            'total_formatted' => '$' . number_format($total / 100, 2),
            'currency' => 'USD'
        ];
    }

    /**
     * Create a new order
     *
     * @param array $orderData
     * @param int $userId
     * @return array
     */
    public function createOrder(array $orderData, int $userId): array
    {
        return DB::transaction(function () use ($orderData, $userId) {
            $currency = strtoupper($orderData['currency'] ?? 'USD');
            
            // Calculate totals and validate stock
            $orderCalculation = $this->calculateOrderTotals($orderData['items']);
            
            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'subtotal_cents' => $orderCalculation['subtotal'],
                'tax_cents' => $orderCalculation['tax'],
                'total_cents' => $orderCalculation['total'],
                'currency' => $currency,
                'status' => 'pending',
            ]);

            // Create order items and decrease stock
            $this->createOrderItems($order->id, $orderCalculation['items']);

            // Create payment record
            $payment = $this->createPaymentRecord($order->id, $orderCalculation['total'], $currency);

            return [
                'order' => $order->load('items.product'),
                'payment' => $payment,
                'order_summary' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_formatted' => '$' . number_format($orderCalculation['total'] / 100, 2),
                    'currency' => $currency
                ]
            ];
        });
    }

    /**
     * Simulate payment processing
     *
     * @param int $orderId
     * @param int $userId
     * @return array
     */
    public function simulatePayment(int $orderId, int $userId): array
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->with('payment')
            ->firstOrFail();

        $payment = $order->payment;
        
        if ($payment->status !== 'pending') {
            throw new ValidationException(null, 'Payment already processed');
        }

        // Simulate payment processing with 90% success rate
        $success = rand(1, 100) <= 90;
        
        if ($success) {
            $this->processSuccessfulPayment($payment, $order);
            
            return [
                'success' => true,
                'message' => 'Payment successful',
                'data' => [
                    'order' => $order->fresh(),
                    'payment' => $payment,
                    'transaction_id' => $payment->payload['transaction_id']
                ]
            ];
        } else {
            $this->processFailedPayment($payment, $order);
            
            return [
                'success' => false,
                'message' => 'Payment failed',
                'data' => [
                    'order' => $order->fresh(),
                    'payment' => $payment,
                    'error' => 'Insufficient funds'
                ]
            ];
        }
    }

    /**
     * Calculate tax for order
     *
     * @param int $subtotal
     * @return int
     */
    protected function calculateTax(int $subtotal): int
    {
        return (int)round($subtotal * 0.08); // 8% tax
    }

    /**
     * Calculate order totals
     *
     * @param array $items
     * @return array
     */
    protected function calculateOrderTotals(array $items): array
    {
        $subtotal = 0;
        $validatedItems = [];

        foreach ($items as $line) {
            $product = Product::lockForUpdate()->find($line['product_id']);
            
            if (!$product || $product->stock < $line['quantity']) {
                throw new ValidationException(null, "Insufficient stock for product ID {$line['product_id']}");
            }
            
            $lineTotal = $product->price_cents * $line['quantity'];
            $subtotal += $lineTotal;
            
            $validatedItems[] = [
                'product' => $product,
                'quantity' => $line['quantity'],
                'line_total' => $lineTotal
            ];
        }

        $tax = $this->calculateTax($subtotal);
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'items' => $validatedItems
        ];
    }

    /**
     * Create order items and decrease stock
     *
     * @param int $orderId
     * @param array $items
     * @return void
     */
    protected function createOrderItems(int $orderId, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $item['product']->id,
                'unit_price_cents' => $item['product']->price_cents,
                'quantity' => $item['quantity'],
                'line_total_cents' => $item['line_total'],
            ]);
            
            $item['product']->decrement('stock', $item['quantity']);
        }
    }

    /**
     * Create payment record
     *
     * @param int $orderId
     * @param int $amount
     * @param string $currency
     * @return Payment
     */
    protected function createPaymentRecord(int $orderId, int $amount, string $currency): Payment
    {
        return Payment::create([
            'order_id' => $orderId,
            'provider' => 'simulated',
            'status' => 'pending',
            'amount_cents' => $amount,
            'currency' => $currency,
        ]);
    }

    /**
     * Process successful payment
     *
     * @param Payment $payment
     * @param Order $order
     * @return void
     */
    protected function processSuccessfulPayment(Payment $payment, Order $order): void
    {
        $payment->update([
            'status' => 'succeeded',
            'payload' => [
                'simulated' => true,
                'transaction_id' => 'SIM_' . uniqid(),
                'processed_at' => now()->toISOString()
            ]
        ]);
        
        $order->update(['status' => 'paid']);
    }

    /**
     * Process failed payment
     *
     * @param Payment $payment
     * @param Order $order
     * @return void
     */
    protected function processFailedPayment(Payment $payment, Order $order): void
    {
        $payment->update([
            'status' => 'failed',
            'payload' => [
                'simulated' => true,
                'error' => 'Insufficient funds',
                'failed_at' => now()->toISOString()
            ]
        ]);
        
        $order->update(['status' => 'failed']);
    }
} 