<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test idempotent add operation using firstOrCreate.
     */
    public function test_wishlist_firstOrCreate_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // First call creates the wishlist
        $wishlist1 = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Second call returns the same wishlist
        $wishlist2 = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($wishlist1->id, $wishlist2->id);
        $this->assertEquals(1, Wishlist::where('user_id', $user->id)->count());
    }

    /**
     * Test wishlist count for user.
     */
    public function test_wishlist_count_for_user(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $count = Wishlist::where('user_id', $user->id)->count();
        $this->assertEquals(5, $count);
    }

    /**
     * Test bulk wishlist deletion for user.
     */
    public function test_bulk_wishlist_deletion_for_user(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $deletedCount = Wishlist::where('user_id', $user->id)->delete();

        $this->assertEquals(5, $deletedCount);
        $this->assertEquals(0, Wishlist::where('user_id', $user->id)->count());
    }

    /**
     * Test checking if product is in wishlist.
     */
    public function test_check_if_product_is_in_wishlist(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);

        $exists1 = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product1->id)
            ->exists();
        
        $exists2 = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product2->id)
            ->exists();

        $this->assertTrue($exists1);
        $this->assertFalse($exists2);
    }

    /**
     * Test wishlist isolation between users.
     */
    public function test_wishlist_isolation_between_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        $user1Count = Wishlist::where('user_id', $user1->id)->count();
        $user2Count = Wishlist::where('user_id', $user2->id)->count();

        $this->assertEquals(1, $user1Count);
        $this->assertEquals(0, $user2Count);
    }

    /**
     * Test getting wishlist with product details.
     */
    public function test_getting_wishlist_with_product_details(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $wishlistWithProducts = Wishlist::where('user_id', $user->id)
            ->with('product')
            ->get();

        $this->assertCount(3, $wishlistWithProducts);
        
        foreach ($wishlistWithProducts as $wishlistItem) {
            $this->assertNotNull($wishlistItem->product);
            $this->assertInstanceOf(Product::class, $wishlistItem->product);
        }
    }

    /**
     * Test removing specific product from wishlist.
     */
    public function test_removing_specific_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Wishlist::create(['user_id' => $user->id, 'product_id' => $product1->id]);
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product2->id]);

        Wishlist::where('user_id', $user->id)
            ->where('product_id', $product1->id)
            ->delete();

        $this->assertEquals(1, Wishlist::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product2->id,
        ]);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product1->id,
        ]);
    }

    /**
     * Test idempotent delete behavior.
     */
    public function test_idempotent_delete_behavior(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // First delete - nothing to delete
        $deleted1 = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        // Create wishlist item
        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Second delete - deletes the item
        $deleted2 = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        // Third delete - nothing to delete again
        $deleted3 = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        $this->assertEquals(0, $deleted1);
        $this->assertEquals(1, $deleted2);
        $this->assertEquals(0, $deleted3);
    }
}
