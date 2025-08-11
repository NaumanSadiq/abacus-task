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
    public function createOrder(Request $r)
    {
        $payload = $r->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'sometimes|string|in:usd,eur,pkr'
        ]);
        $currency = $payload['currency'] ?? 'usd';

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
            $tax = (int)round($subtotal * 0.00); // adjust if needed
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
                'order' => $order->load('items.product'),
                'payment' => $payment,
            ], 201);
        });
    }

    // Simulated capture (success/fail)
    public function simulatePayment(Request $r, Order $order): \Illuminate\Http\JsonResponse
    {
        abort_if($order->user_id !== $r->user()->id, 403);
        $payment = $order->payment;
        if ($payment->status !== 'pending') return response()->json(['message' => 'Already processed'], 409);

        // flip a "success"
        $payment->update(['status' => 'succeeded', 'payload' => ['simulated' => true]]);
        $order->update(['status' => 'paid']);

        return response()->json(['order' => $order, 'payment' => $payment]);
    }
}
