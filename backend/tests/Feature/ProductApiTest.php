<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_products_requires_auth(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_can_create_product_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $payload = [
            'name' => 'Produto Teste',
            'sku' => 'SKU-' . uniqid(),
            'price' => 10.5,
            'stock' => 2,
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->postJson('/api/products', $payload)->assertStatus(201)->assertJsonFragment([
            'name' => 'Produto Teste',
        ]);
    }
}
