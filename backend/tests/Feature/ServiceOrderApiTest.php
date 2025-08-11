<?php

namespace Tests\Feature;

use App\Models\{User, Client, Product, Service, ServiceOrder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_auth(): void
    {
        $this->getJson('/api/service-orders')->assertStatus(401);
    }

    public function test_can_create_order_with_items(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $client = Client::factory()->create();
        $product = Product::query()->create(['name'=>'P','price'=>10,'stock'=>5]);
        $service = Service::query()->create(['name'=>'S','price'=>20]);

        $payload = [
            'client_id' => $client->id,
            'products' => [['id'=>$product->id,'quantity'=>2]],
            'services' => [['id'=>$service->id,'quantity'=>1]],
        ];

        $res = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->postJson('/api/service-orders', $payload)->assertStatus(201)->json();

        $this->assertEquals(40, (float) $res['total']);
        $this->assertEquals($client->id, $res['client_id']);
    }
}
