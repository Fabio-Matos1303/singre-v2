<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    public function test_clients_crud_and_filters(): void
    {
        $headers = $this->authHeaders();

        // create
        $c1 = $this->withHeaders($headers)->postJson('/api/clients', [
            'name' => 'Alice', 'email' => 'alice@example.com'
        ])->assertStatus(201)->json('data');
        $c2 = $this->withHeaders($headers)->postJson('/api/clients', [
            'name' => 'Bob', 'email' => 'bob@example.com'
        ])->assertStatus(201)->json('data');

        // show
        $this->withHeaders($headers)->getJson('/api/clients/'.$c1['id'])->assertOk()->assertJsonFragment(['email'=>'alice@example.com']);

        // update
        $this->withHeaders($headers)->putJson('/api/clients/'.$c2['id'], [
            'name' => 'Bob Jr'
        ])->assertOk()->assertJsonFragment(['name'=>'Bob Jr']);

        // filters q, per_page, sort, order
        $list = $this->withHeaders($headers)->getJson('/api/clients?q=ali&per_page=1&sort=name&order=asc')
            ->assertOk()->json();
        $this->assertEquals(1, $list['per_page']);
        $this->assertTrue(collect($list['data'])->pluck('email')->contains('alice@example.com'));

        // delete
        $this->withHeaders($headers)->deleteJson('/api/clients/'.$c1['id'])->assertOk();
    }

    public function test_products_crud_and_filters(): void
    {
        $headers = $this->authHeaders();

        // create
        $p1 = $this->withHeaders($headers)->postJson('/api/products', [
            'name' => 'Teclado', 'sku' => 'T-1', 'price' => 100
        ])->assertStatus(201)->json('data');
        $p2 = $this->withHeaders($headers)->postJson('/api/products', [
            'name' => 'Mouse', 'sku' => 'M-1', 'price' => 50
        ])->assertStatus(201)->json('data');

        // show
        $this->withHeaders($headers)->getJson('/api/products/'.$p1['id'])->assertOk()->assertJsonFragment(['sku'=>'T-1']);

        // update
        $this->withHeaders($headers)->putJson('/api/products/'.$p2['id'], [
            'price' => 60
        ])->assertOk()->assertJsonPath('data.price', '60.00');

        // filters
        $list = $this->withHeaders($headers)->getJson('/api/products?q=tec&per_page=1&sort=price&order=desc')
            ->assertOk()->json();
        $this->assertEquals(1, $list['per_page']);
        $this->assertTrue(collect($list['data'])->pluck('sku')->contains('T-1'));

        // delete
        $this->withHeaders($headers)->deleteJson('/api/products/'.$p1['id'])->assertOk();
    }

    public function test_services_crud_and_filters(): void
    {
        $headers = $this->authHeaders();

        // create
        $s1 = $this->withHeaders($headers)->postJson('/api/services', [
            'name' => 'Instalação', 'price' => 200
        ])->assertStatus(201)->json('data');
        $s2 = $this->withHeaders($headers)->postJson('/api/services', [
            'name' => 'Manutenção', 'price' => 150
        ])->assertStatus(201)->json('data');

        // show
        $this->withHeaders($headers)->getJson('/api/services/'.$s1['id'])->assertOk()->assertJsonFragment(['name'=>'Instalação']);

        // update
        $this->withHeaders($headers)->putJson('/api/services/'.$s2['id'], [
            'price' => 175
        ])->assertOk()->assertJsonPath('data.price', '175.00');

        // filters
        $list = $this->withHeaders($headers)->getJson('/api/services?q=Manu&per_page=1&sort=price&order=asc')
            ->assertOk()->json();
        $this->assertEquals(1, $list['per_page']);
        $this->assertTrue(collect($list['data'])->pluck('name')->contains('Manutenção'));

        // delete
        $this->withHeaders($headers)->deleteJson('/api/services/'.$s1['id'])->assertOk();
    }
}
