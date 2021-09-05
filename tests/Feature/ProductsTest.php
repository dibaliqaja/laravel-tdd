<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * product page contains empty
     *
     * @return void
     */
    public function test_product_page_contains_empty_products_table()
    {
        $response = $this->get('/product');

        $response->assertStatus(200);
        $response->assertSee('No products found.');
    }

    /**
     * product page contains non empty
     *
     * @return void
     */
    public function test_product_page_contains_non_empty_products_table()
    {
        $product = Product::create([
            'name' => 'Bottle 1000',
            'price' => 5000
        ]);

        $response = $this->get('/product');

        $response->assertStatus(200);
        $response->assertDontSee('No products found.');        
        $view_products = $response->viewData('products');
        // dd($view_products);
        $this->assertEquals($product->name, $view_products->first()->name);
    }
}
