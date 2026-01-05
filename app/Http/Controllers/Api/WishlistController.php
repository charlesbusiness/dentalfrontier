<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateWishlistRequest;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    
    protected $wishlistService;
    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Display the authenticated user's wishlist.
     */
    public function getUserWishLists(Request $request)
    {
        return $this->wishlistService->getUserWishLists($request);
    }

    /**
     * Add a product to the user's wishlist.
     * Idempotent: Returns 200 if product already exists in wishlist.
     */
    public function addProductToWishlist(CreateWishlistRequest $request)
    {
        return $this->wishlistService->addProductToWishlist($request);
    }

    /**
     * Remove a product from the user's wishlist.
     * Idempotent: Returns 200 even if product doesn't exist in wishlist.
     */
    public function removeAnItemFromWishlist(Request $request, $productId)
    {
        return $this->wishlistService->removeAnItemFromWishlist($request, $productId);
    }

    /**
     * Clear all items from the user's wishlist.
     */
    public function clearUserWishList(Request $request)
    {
        return $this->wishlistService->clearUserWishList($request);
    }
}
