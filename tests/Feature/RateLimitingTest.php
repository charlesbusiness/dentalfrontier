<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test authentication endpoints are rate limited.
     */
    public function test_login_endpoint_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Make 5 requests (at the limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

            $response->assertStatus(200);
        }

        // The 6th request should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'retry_after',
            ]);
    }

    /**
     * Test registration endpoint is rate limited.
     */
    public function test_registration_endpoint_is_rate_limited(): void
    {
        // Make 5 requests (at the limit)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/register', [
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        // The 6th request should be rate limited
        $response = $this->postJson('/api/register', [
            'name' => 'User 6',
            'email' => 'user6@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'retry_after',
            ]);
    }

    /**
     * Test wishlist endpoints are rate limited.
     */
    public function test_wishlist_endpoints_are_rate_limited(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $products = Product::factory()->count(35)->create();

        // Make 30 requests (at the limit)
        for ($i = 0; $i < 30; $i++) {
            $response = $this->postJson('/api/wishlist', [
                'product_id' => $products[$i]->id,
            ]);

            $this->assertContains($response->status(), [200, 201]);
        }

        // The 31st request should be rate limited
        $response = $this->postJson('/api/wishlist', [
            'product_id' => $products[30]->id,
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'retry_after',
            ]);
    }

    /**
     * Test product listing endpoint is rate limited.
     */
    public function test_product_listing_is_rate_limited(): void
    {
        Product::factory()->count(5)->create();

        // Make 100 requests (at the limit)
        for ($i = 0; $i < 20; $i++) {
            $response = $this->getJson('/api/products');
            $response->assertStatus(200);
        }

        // The 101st request should be rate limited
        $response = $this->getJson('/api/products');

        $response->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'retry_after',
            ]);
    }

    /**
     * Test rate limit headers are present.
     */
    public function test_rate_limit_headers_are_present(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }
}
