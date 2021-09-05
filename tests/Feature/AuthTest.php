<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_successfully()
    {
        // create a user
        User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // post to /login
        $response = $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => 'password'
        ]);
        
        // assert redirect 302 to /home
        $response->assertStatus(302);
        $response->assertRedirect('/home');

    }

    public function test_authenticated_user_can_access_products_table()
    {
        // create a user
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        // go to homepage
        $response = $this->actingAs($user)->get('/product');

        // assert status 200
        $response->assertStatus(200);
    }

    public function test_authenticated_user_cannot_access_products_table()
    {
        // go to homepage
        $response = $this->get('/product');

        // assert status 302
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
