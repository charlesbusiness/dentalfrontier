<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBusinessLogicTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test product is in stock.
     */
    public function test_product_stock_validation(): void
    {
        $inStockProduct = Product::factory()->create(['stock' => 10]);
        $outOfStockProduct = Product::factory()->create(['stock' => 0]);

        $this->assertGreaterThan(0, $inStockProduct->stock);
        $this->assertEquals(0, $outOfStockProduct->stock);
    }



    /**
     * Test product search by name.
     */
    public function test_product_can_be_searched_by_name(): void
    {
        Product::factory()->create(['name' => 'Laptop Computer']);
        Product::factory()->create(['name' => 'Desktop Computer']);
        Product::factory()->create(['name' => 'Smartphone']);

        $results = Product::where('name', 'LIKE', '%Computer%')->get();

        $this->assertCount(2, $results);
    }

    /**
     * Test product filtering by price range.
     */
    public function test_product_can_be_filtered_by_price_range(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 100.00]);
        Product::factory()->create(['price' => 150.00]);
        Product::factory()->create(['price' => 200.00]);

        $results = Product::whereBetween('price', [75, 175])->get();

        $this->assertCount(2, $results);
    }

    /**
     * Test product availability check.
     */
    public function test_product_availability_logic(): void
    {
        $availableProduct = Product::factory()->create(['stock' => 5]);
        $unavailableProduct = Product::factory()->create(['stock' => 0]);

        $this->assertTrue($availableProduct->stock > 0);
        $this->assertFalse($unavailableProduct->stock > 0);
    }
}
