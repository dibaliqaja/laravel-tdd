<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductsTest extends TestCase
{
    // /**
    //  * product page contains empty
    //  *
    //  * @return void
    //  */
    // public function test_product_page_contains_empty_products_table()
    // {
    //     $response = $this->get('/product');

    //     $response->assertStatus(200);
    //     $response->assertSee('No products found.');
    // }

    /**
     * product page contains non empty
     *
     * @return void
     */
    public function test_product_page_contains_non_empty_products_table()
    {
        $response = $this->get('/product');

        $response->assertStatus(200);
        $response->assertDontSee('No products found.');
    }
}
