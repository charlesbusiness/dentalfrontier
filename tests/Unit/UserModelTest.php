<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can be created with valid attributes.
     */
    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test user password is hashed.
     */
    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext',
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(password_verify('plaintext', $user->password));
    }

    /**
     * Test user has wishlists relationship.
     */
    public function test_user_has_wishlists_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $user->wishlists()
        );
    }

    /**
     * Test user has wishlist products relationship.
     */
    public function test_user_has_wishlist_products_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $user->wishlistProducts()
        );
    }

    /**
     * Test user can access wishlist products.
     */
    public function test_user_can_access_wishlist_products(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        $user->wishlistProducts()->attach($products->pluck('id'));

        $this->assertCount(3, $user->wishlistProducts);
        $this->assertEquals(
            $products->pluck('id')->sort()->values(),
            $user->wishlistProducts->pluck('id')->sort()->values()
        );
    }

}
