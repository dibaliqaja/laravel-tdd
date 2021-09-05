<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Factories\ProductFactory;
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
        // create a user
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // go to homepage
        $response = $this->actingAs($user)->get('/product');

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

        // create a user
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // go to homepage
        $response = $this->actingAs($user)->get('/product');

        $response->assertStatus(200);
        $response->assertDontSee('No products found.');        
        $view_products = $response->viewData('products');
        // dd($view_products); // for view testing die_dump() products data on terminal
        $this->assertEquals($product->name, $view_products->first()->name);
    }

    public function test_paginated_products_table_doesnt_show_11th_record()
    {
        $products = Product::factory(11)->create();
        
        // info($products); // info(); is function for catch data to log [/storage/logs/laravel.log]

        // for($i=1; $i<=11; $i++) {
        //     $product = Product::create([
        //         'name' => 'Bottle ' . $i,
        //         'price' => rand(10, 99)
        //     ]);
        // }

        // create a user
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // go to homepage
        $response = $this->actingAs($user)->get('/product');

        $response->assertDontSee($products->last()->name); 
    }
}
