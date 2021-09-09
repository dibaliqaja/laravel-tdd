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

    private $user;

    private function create_user($is_admin = 0)
    {
        $this->user = User::factory()->create([
            'email' => ($is_admin) ? 'admin@admin.com' : 'user@user.com',
            'password' => bcrypt('password'),
            'is_admin' => $is_admin,
        ]);
    }

    /**
     * product page contains empty
     *
     * @return void
     */
    public function test_product_page_contains_empty_products_table()
    {
        $this->create_user();

        // go to homepage
        $response = $this->actingAs($this->user)->get('/products');

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

        $this->create_user();

        // go to homepage
        $response = $this->actingAs($this->user)->get('/products');

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

        $this->create_user();

        // go to homepage
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertDontSee($products->last()->name); 
    }

    public function test_admin_can_see_product_create_button()
    {
        $this->create_user(1);

        $response = $this->actingAs($this->user)->get('products');

        $response->assertStatus(200);
        $response->assertSee('Add new product');
    }

    public function test_non_admin_cannot_see_product_create_button()
    {
        $this->create_user();

        $response = $this->actingAs($this->user)->get('products');

        $response->assertStatus(200);
        $response->assertDontSee('Add new product');
    }

    public function test_admin_can_access_products_create_page()
    {
        $this->create_user(1);

        $response = $this->actingAs($this->user)->get('products/create');
        
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_products_create_page()
    {
        $this->create_user();

        $response = $this->actingAs($this->user)->get('products/create');
        
        $response->assertStatus(403);
    }

    public function test_store_product_exists_in_database()
    {
        $this->create_user(1);

        $response = $this->actingAs($this->user)->post('products', [
            'name' => 'New product',
            'price' => 99.99,
        ]);

        $response->assertRedirect('products');

        $this->assertDatabaseHas('products', [
            'name' => 'New product',
            'price' => 99.99,
        ]);

        $product = Product::orderBy('id', 'desc')->first();
        
        $this->assertEquals('New product', $product->name);
        $this->assertEquals(99.99, $product->price);
    }

    public function test_edit_product_form_contains_correct_name_and_price()
    {
        $this->create_user(1);
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)->get('/products/'.$product->id.'/edit');

        $response->assertStatus(200);
        $response->assertSee('value="'.$product->name.'"', false);
        $response->assertSee('value="'.$product->price.'"', false);
    }

    public function test_update_product_correct_validation_error()
    {
        $this->create_user(1);

        $product = Product::factory()->create();
        $response = $this->actingAs($this->user)
                ->put('/products/' . $product->id , 
                ['name' => 'Box', 'price' => 99.99]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_product_json_correct_validation_error()
    {
        $this->create_user(1);

        $product = Product::factory()->create();
        $response = $this->actingAs($this->user)
                ->put('/products/' . $product->id ,
                ['name' => 'Box', 'price' => 99.99],
                ['Accept' => 'Application/json']);

        $response->assertStatus(422);
    }

    public function test_delete_product_no_longer_exists_in_database()
    {
        $this->create_user(1);

        $product = Product::factory()->create();
        $this->assertEquals(1, Product::count());

        $response = $this->actingAs($this->user)->delete('products/' . $product->id);
        $response->assertStatus(302);
        $this->assertEquals(0, Product::count());
    }
}
