<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable throttling for feature tests
        $this->withoutMiddleware(ThrottleRequests::class);

        // Makes failures explicit instead of silent exits
        $this->withoutExceptionHandling();
    }

    /**
     * Test retrieving all products.
     */
    public function test_can_retrieve_all_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'stock'],
                ],
            ])
            ->assertJson(['success' => true]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test retrieving a single product.
     */
    public function test_can_retrieve_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'description', 'price', 'stock'],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
            ]);
    }

    /**
     * Test retrieving a non-existent product.
     */
    public function test_cannot_retrieve_non_existent_product(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    /**
     * Test product validation.
     */
    public function test_product_creation_validates_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/products', [
            'name' => '',
            'price' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson(['success' => false]);
    }
}
