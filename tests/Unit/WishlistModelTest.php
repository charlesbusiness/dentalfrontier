<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test wishlist can be created with valid attributes.
     */
    public function test_wishlist_can_be_created(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Wishlist::class, $wishlist);
        $this->assertEquals($user->id, $wishlist->user_id);
        $this->assertEquals($product->id, $wishlist->product_id);
    }

    /**
     * Test wishlist belongs to user.
     */
    public function test_wishlist_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $wishlist->user()
        );
        $this->assertEquals($user->id, $wishlist->user->id);
    }

    /**
     * Test wishlist belongs to product.
     */
    public function test_wishlist_belongs_to_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $wishlist->product()
        );
        $this->assertEquals($product->id, $wishlist->product->id);
    }

    /**
     * Test wishlist unique constraint prevents duplicate entries.
     */
    public function test_wishlist_prevents_duplicate_entries(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create duplicate
        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test wishlist cascades on user deletion.
     */
    public function test_wishlist_cascades_on_user_deletion(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $wishlistId = $wishlist->id;
        $user->delete();

        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlistId,
        ]);
    }

    /**
     * Test wishlist cascades on product deletion.
     */
    public function test_wishlist_cascades_on_product_deletion(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $wishlistId = $wishlist->id;
        $product->delete();

        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlistId,
        ]);
    }

    /**
     * Test wishlist fillable attributes.
     */
    public function test_wishlist_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertEquals($user->id, $wishlist->user_id);
        $this->assertEquals($product->id, $wishlist->product_id);
    }

    /**
     * Test wishlist timestamps are set.
     */
    public function test_wishlist_timestamps_are_set(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertNotNull($wishlist->created_at);
        $this->assertNotNull($wishlist->updated_at);
    }

    /**
     * Test multiple users can wishlist same product.
     */
    public function test_multiple_users_can_wishlist_same_product(): void
    {
        $users = User::factory()->count(3)->create();
        $product = Product::factory()->create();

        foreach ($users as $user) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $this->assertEquals(3, Wishlist::where('product_id', $product->id)->count());
    }

    /**
     * Test user can wishlist multiple products.
     */
    public function test_user_can_wishlist_multiple_products(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $this->assertEquals(5, Wishlist::where('user_id', $user->id)->count());
    }
}
