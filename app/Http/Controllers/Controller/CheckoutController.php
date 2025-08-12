<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function viewCheckout(Request $request)
    {
        $cartItems = $request->input('items', []);
        
        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'No items in cart'
            ], 400);
        }

        $checkoutData = [];
        $subtotal = 0;
        $errors = [];

        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id'] ?? null);
            
            if (!$product) {
                $errors[] = "Product with ID {$item['product_id']} not found";
                continue;
            }

            $quantity = $item['quantity'] ?? 1;
            
            if ($product->stock < $quantity) {
                $errors[] = "Insufficient stock for {$product->name}. Available: {$product->stock}";
                continue;
            }

            $lineTotal = $product->price_cents * $quantity;
            $subtotal += $lineTotal;

            $checkoutData[] = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price_cents' => $product->price_cents,
                    'price_formatted' => '$' . number_format($product->price_cents / 100, 2),
                    'stock' => $product->stock
                ],
                'quantity' => $quantity,
                'line_total_cents' => $lineTotal,
                'line_total_formatted' => '$' . number_format($lineTotal / 100, 2)
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $errors
            ], 422);
        }

        $tax = (int)round($subtotal * 0.08); // 8% tax
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $checkoutData,
                'subtotal_cents' => $subtotal,
                'subtotal_formatted' => '$' . number_format($subtotal / 100, 2),
                'tax_cents' => $tax,
                'tax_formatted' => '$' . number_format($tax / 100, 2),
                'total_cents' => $total,
                'total_formatted' => '$' . number_format($total / 100, 2),
                'currency' => 'USD'
            ]
        ]);
    }

    public function createOrder(Request $r)
    {
        $payload = $r->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'sometimes|string|in:usd,eur,pkr'
        ]);
        $currency = strtoupper($payload['currency'] ?? 'USD');

        return DB::transaction(function () use ($r, $payload, $currency) {
            // calc totals and check stock
            $subtotal = 0;
            $items = [];
            foreach ($payload['items'] as $line) {
                $p = Product::lockForUpdate()->find($line['product_id']);
                abort_if($p->stock < $line['quantity'], 422, "Insufficient stock for {$p->name}");
                $lineTotal = $p->price_cents * $line['quantity'];
                $subtotal += $lineTotal;
                $items[] = ['product' => $p, 'qty' => $line['quantity'], 'line_total' => $lineTotal];
            }
            $tax = (int)round($subtotal * 0.08); // 8% tax
            $total = $subtotal + $tax;

            $order = Order::create([
                'user_id' => $r->user()->id,
                'subtotal_cents' => $subtotal,
                'tax_cents' => $tax,
                'total_cents' => $total,
                'currency' => $currency,
                'status' => 'pending',
            ]);

            foreach ($items as $it) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $it['product']->id,
                    'unit_price_cents' => $it['product']->price_cents,
                    'quantity' => $it['qty'],
                    'line_total_cents' => $it['line_total'],
                ]);
                $it['product']->decrement('stock', $it['qty']);
            }

            // Create a pending payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'simulated',
                'status' => 'pending',
                'amount_cents' => $total,
                'currency' => $currency,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order' => $order->load('items.product'),
                    'payment' => $payment,
                    'order_summary' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'total_formatted' => '$' . number_format($total / 100, 2),
                        'currency' => $currency
                    ]
                ]
            ], 201);
        });
    }

    // Simulated capture (success/fail)
    public function simulatePayment(Request $r, Order $order): \Illuminate\Http\JsonResponse
    {
        abort_if($order->user_id !== $r->user()->id, 403);
        $payment = $order->payment;
        if ($payment->status !== 'pending') return response()->json(['message' => 'Already processed'], 409);

        // Simulate payment processing with 90% success rate
        $success = rand(1, 100) <= 90;
        
        if ($success) {
            $payment->update([
                'status' => 'succeeded', 
                'payload' => [
                    'simulated' => true,
                    'transaction_id' => 'SIM_' . uniqid(),
                    'processed_at' => now()->toISOString()
                ]
            ]);
            $order->update(['status' => 'paid']);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'data' => [
                    'order' => $order->fresh(),
                    'payment' => $payment,
                    'transaction_id' => $payment->payload['transaction_id']
                ]
            ]);
        } else {
            $payment->update([
                'status' => 'failed', 
                'payload' => [
                    'simulated' => true,
                    'error' => 'Insufficient funds',
                    'failed_at' => now()->toISOString()
                ]
            ]);
            $order->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'data' => [
                    'order' => $order->fresh(),
                    'payment' => $payment,
                    'error' => 'Insufficient funds'
                ]
            ], 422);
        }
    }
}
