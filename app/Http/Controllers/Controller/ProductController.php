<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select('id', 'name', 'description', 'price_cents', 'stock')
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price_cents' => $product->price_cents,
                    'price_formatted' => '$' . number_format($product->price_cents / 100, 2),
                    'stock' => $product->stock,
                    'available' => $product->stock > 0
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'price_formatted' => '$' . number_format($product->price_cents / 100, 2),
                'stock' => $product->stock,
                'available' => $product->stock > 0
            ]
        ]);
    }
}
