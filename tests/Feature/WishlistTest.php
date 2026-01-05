<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving empty wishlist.
     */
    public function test_authenticated_user_can_retrieve_empty_wishlist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Wishlists retrieved successfully',
                'data' => [],
                'success' => true,
            ]);
    }

    /**
     * Test adding a product to wishlist.
     */
    public function test_authenticated_user_can_add_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $response = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'wishlist_id',
                    'product' => ['id', 'name', 'description', 'price'],
                ],
            ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test adding non-existent product to wishlist.
     */
    public function test_cannot_add_non_existent_product_to_wishlist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wishlist', [
            'product_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    /**
     * Test cannot add duplicate product to wishlist.
     */
    public function test_adding_duplicate_product_is_idempotent(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        // Add product first time
        $response1 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);

        $response1->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'wishlist_id',
                    'product',
                ],
            ]);

        // Add same product again
        $response2 = $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'message' => 'Product is already in your wishlist',
                'success' => true,
            ]);

        $this->assertEquals(
            1,
            Wishlist::where('user_id', $user->id)->count()
        );
    }

    /**
     * Test retrieving wishlist with products.
     */
    public function test_authenticated_user_can_retrieve_wishlist_with_products(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'stock'],
                ],
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test removing a product from wishlist.
     */
    public function test_authenticated_user_can_remove_product_from_wishlist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson('/api/wishlist/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product removed from wishlist successfully',
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test removing non-existent product from wishlist is idempotent.
     */
    public function test_removing_non_existent_product_is_idempotent(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $response = $this->deleteJson('/api/wishlist/' . $product->id);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Product not found in your wishlist',
                'success' => false,
            ]);
    }

    /**
     * Test clearing entire wishlist.
     */
    public function test_authenticated_user_can_clear_wishlist(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(5)->create();

        foreach ($products as $product) {
            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }

        $response = $this->deleteJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Wishlist cleared successfully',
                'data' => [
                    'items_removed' => 5,
                ],
                'success' => true,
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test unauthenticated user cannot access wishlist.
     */
    public function test_unauthenticated_user_cannot_access_wishlist(): void
    {
        $this->getJson('/api/wishlist')->assertStatus(401);
        $this->postJson('/api/wishlist', ['product_id' => 1])->assertStatus(401);
        $this->deleteJson('/api/wishlist/1')->assertStatus(401);
    }

    /**
     * Test users can only access their own wishlist.
     */
    public function test_users_can_only_access_their_own_wishlist(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
        ]);

        Sanctum::actingAs($user2);

        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'success' => true,
            ]);
    }
}
