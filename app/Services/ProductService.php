<?php

namespace App\Services;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\ProductSearchRequest;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class ProductService
{
    use ApiResponse;

    /**
     * Display a listing of all products.
     */
    public function getAllProducts(ProductSearchRequest $request)
    {
        try {

            $requestHasPerPage = $request->filled('perPage');
            $products = Product::query()
                ->select('id', 'name', 'description', 'price', 'stock')
                ->filter($request->only(['name', 'min_price', 'max_price']));
            $data = $requestHasPerPage ? $this->transformPaginatedData($products->paginate($request->perPage)) : ['data' => $products->get()];

            return $this->successfulResponse($data, 'Products retrieved successfully');
        } catch (Throwable $e) {
            return $this->failedResponse(null, 'Failed to retrieve products', $e->getCode() ?? 500);
        }
    }

    public function getAProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            return $this->successfulResponse($product, "Product with Id $id retrieved successfully");
        } catch (ModelNotFoundException $e) {
            return $this->failedResponse(null, "Product with Id $id not found", 404);
        } catch (Throwable $e) {
            return $this->failedResponse(null, "Failed to retrieve product with Id $id", 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function createProduct(CreateProductRequest $request)
    {
        try {
            $validated = $request->validated();
            $product = Product::create($validated);
            return $this->successfulResponse($product, 'Product created successfully', 201);
        } catch (Throwable $e) {
            return $this->failedResponse(null, "Failed to create product", 500);
        }
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|min:0',
                'stock' => 'sometimes|required|integer|min:0',
            ]);

            $product->update($validated);
            return $this->successfulResponse($product, 'Product updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->failedResponse(null, "Product with Id $id not found", 404);
        } catch (Throwable $e) {
            return $this->failedResponse(null, "Failed to update product", 500);
        }
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return $this->successfulResponse(null, 'Product deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->failedResponse(null, "Product with Id $id not found", 404);
        } catch (Throwable $e) {
            return $this->failedResponse(null, "Failed to delete product", 500);
        }
    }
}
