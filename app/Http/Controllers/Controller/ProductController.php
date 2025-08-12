<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all available products
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();
            $formattedProducts = $this->productService->formatProducts($products);
            
            return $this->successResponse($formattedProducts, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products: ' . $e->getMessage());
        }
    }

    /**
     * Get a single product
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $formattedProduct = $this->productService->formatProduct($product);
            
            return $this->successResponse($formattedProduct, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product: ' . $e->getMessage());
        }
    }
}
