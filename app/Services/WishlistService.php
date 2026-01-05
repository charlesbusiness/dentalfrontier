<?php

namespace App\Services;

use App\Http\Requests\CreateWishlistRequest;
use App\Models\Product;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class WishlistService
{
    use ApiResponse;

    /**
     * Display a listing of paginated user's products in a wishlist.
     * @param Request $request
     */
    public function getUserWishLists(Request $request)
    {
        try {
            $requestHasPerPage = $request->filled('perPage');
            $user = $request->user();
            $wishlistQuery = $user->wishlistProducts();

            $data = $requestHasPerPage ? $this->transformPaginatedData($wishlistQuery->paginate($request->perPage)) : ['data' => $wishlistQuery->get()];
            return $this->successfulResponse($data, 'Wishlists retrieved successfully');
        } catch (Throwable $e) {
            return $this->failedResponse(null, 'Failed to retrieve products', $e->getCode(), $e);
        }
    }
    /**
     * Remove a product from the user's wishlist.
     * @param Request $request
     * @param int $productId
     */
    public function removeAnItemFromWishlist(Request $request, $productId)
    {
        try {
            $user = $request->user();

            $wishlist = Wishlist::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$wishlist) {
                $message = 'Product not found in your wishlist';
                return $this->failedResponse(null, $message, 400);
            }
            $wishlist->delete();
            $message = 'Product removed from wishlist successfully';
            return $this->successfulResponse(null, $message);
        } catch (Exception $e) {
            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Failed to remove product from wishlist" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }

    /**
     * Add a product to the user's wishlist.
     * Idempotent: Returns 200 if product already exists in wishlist.
     * @param CreateWishlistRequest $request
     */

    public function addProductToWishlist(CreateWishlistRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();
            $productId = $validated['product_id'];

            // Check if product is already in wishlist
            $wishlist = Wishlist::query()
                ->where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                $message = 'Product is already in your wishlist';
                $code = 200;
            } else {

                $wishlist = Wishlist::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $message = 'Product added to wishlist successfully';
                $code = 201;
            }

            $product = Product::find($productId);
            $data = [
                'wishlist_id' => $wishlist->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                ],
            ];
            return $this->successfulResponse($data, $message, $code);
        } catch (Throwable $e) {
            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Failed to add product to wishlist" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }

    /**
     * Clear all items from the user's wishlist.
     * @param Request $request
     */
    public function clearUserWishList(Request $request)
    {
        try {
            $user = $request->user();
            $deletedCount = Wishlist::query()
                ->where('user_id', $user->id)->delete();
            $data = [
                'items_removed' => $deletedCount
            ];
            return $this->successfulResponse($data, 'Wishlist cleared successfully');
        } catch (Exception $e) {

            $code = $e->getCode() ?? 500;
            $message  = $code === 500 ? "Failed to clear wishlist" : $e->getMessage();
            return $this->failedResponse(null, $message, $code);
        }
    }
}
