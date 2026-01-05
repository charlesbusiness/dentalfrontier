<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\CreateWishlistRequest;
use App\Http\Requests\ProductSearchRequest;
use App\Services\ProductService;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;
    protected $wishlistService;
    public function __construct(ProductService $productService, WishlistService $wishlistService)
    {
        $this->productService = $productService;
        $this->wishlistService = $wishlistService;
    }
    /**
     * Display a listing of all products.
     */
    public function getAllProducts(ProductSearchRequest $request)
    {
        return $this->productService->getAllProducts($request);
    }

    /**
     * Display the specified product.
     */
    public function getAProduct($id)
    {
        return $this->productService->getAProduct($id);
    }


    /**
     * Create a new product.
     */
    public function createProduct(CreateProductRequest $request)
    {
        return $this->productService->createProduct($request);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, $id)
    {
        return $this->productService->updateProduct($request, $id);
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($id)
    {
        return $this->productService->deleteProduct($id);
    }

    public function addProductToWishlist(CreateWishlistRequest $request)
    {
        return $this->wishlistService->addProductToWishlist($request);
    }

}
