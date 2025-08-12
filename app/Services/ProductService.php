<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Get all available products
     *
     * @return Collection
     */
    public function getAllProducts(): Collection
    {
        return Product::select('id', 'name', 'description', 'price_cents', 'stock')
            ->where('stock', '>', 0)
            ->get();
    }

    /**
     * Get a single product by ID
     *
     * @param int $id
     * @return Product|null
     */
    public function getProduct(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Format product data for API response
     *
     * @param Product $product
     * @return array
     */
    public function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price_cents' => $product->price_cents,
            'price_formatted' => '$' . number_format($product->price_cents / 100, 2),
            'stock' => $product->stock,
            'available' => $product->stock > 0
        ];
    }

    /**
     * Format multiple products for API response
     *
     * @param Collection $products
     * @return array
     */
    public function formatProducts(Collection $products): array
    {
        return $products->map(function ($product) {
            return $this->formatProduct($product);
        })->toArray();
    }

    /**
     * Check if product has sufficient stock
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasSufficientStock(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);
        return $product && $product->stock >= $quantity;
    }

    /**
     * Decrease product stock
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);
        if ($product && $product->stock >= $quantity) {
            $product->decrement('stock', $quantity);
            return true;
        }
        return false;
    }
} 