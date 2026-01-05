<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product can be created with valid attributes.
     */
    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals('99.99', $product->price);
        $this->assertEquals(10, $product->stock);
    }

    /**
     * Test product price is cast to decimal.
     */
    public function test_product_price_is_cast_to_decimal(): void
    {
        $product = Product::factory()->create([
            'price' => 100.5,
        ]);

        $this->assertIsString($product->price);
        $this->assertEquals('100.50', $product->price);
    }

    /**
     * Test product stock is cast to integer.
     */
    public function test_product_stock_is_cast_to_integer(): void
    {
        $product = Product::factory()->create([
            'stock' => '50',
        ]);

        $this->assertIsInt($product->stock);
        $this->assertEquals(50, $product->stock);
    }

    /**
     * Test product has wishlists relationship.
     */
    public function test_product_has_wishlists_relationship(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $product->wishlists()
        );
    }

    /**
     * Test product has wishlisted by users relationship.
     */
    public function test_product_has_wishlisted_by_users_relationship(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $product->wishlistedByUsers()
        );
    }

    /**
     * Test product can be wishlisted by multiple users.
     */
    public function test_product_can_be_wishlisted_by_multiple_users(): void
    {
        $product = Product::factory()->create();
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            $user->wishlistProducts()->attach($product->id);
        }

        $this->assertCount(5, $product->wishlistedByUsers);
    }

    /**
     * Test product fillable attributes.
     */
    public function test_product_fillable_attributes(): void
    {
        $product = Product::create([
            'name' => 'New Product',
            'description' => 'New Description',
            'price' => 199.99,
            'stock' => 20,
        ]);

        $this->assertEquals('New Product', $product->name);
        $this->assertEquals('New Description', $product->description);
        $this->assertEquals('199.99', $product->price);
        $this->assertEquals(20, $product->stock);
    }

    /**
     * Test product validation requires name.
     */
    public function test_product_requires_name(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Product::create([
            'description' => 'Description',
            'price' => 50.00,
            'stock' => 5,
        ]);
    }

    /**
     * Test product timestamps are set.
     */
    public function test_product_timestamps_are_set(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);
    }
}
