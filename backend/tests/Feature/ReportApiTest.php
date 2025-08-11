<?php

namespace Tests\Feature;

use App\Models\{User, Client, Product, Service};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
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

    public function test_reports_summary_and_top(): void
    {
        $headers = $this->authHeaders();

        $client = Client::factory()->create();
        $p = Product::query()->create(['name'=>'P1','price'=>10]);
        $s = Service::query()->create(['name'=>'S1','price'=>5]);

        // cria duas OS simples
        $payload = fn($q1,$q2) => [
            'client_id' => $client->id,
            'products' => [['id'=>$p->id,'quantity'=>$q1]],
            'services' => [['id'=>$s->id,'quantity'=>$q2]],
        ];

        $this->withHeaders($headers)->postJson('/api/service-orders', $payload(2,1))->assertStatus(201);
        $this->withHeaders($headers)->postJson('/api/service-orders', $payload(1,2))->assertStatus(201);

        $summary = $this->withHeaders($headers)->getJson('/api/reports/summary')->assertOk()->json();
        $this->assertArrayHasKey('orders', $summary);
        $this->assertGreaterThanOrEqual(2, $summary['orders']['count']);

        $top = $this->withHeaders($headers)->getJson('/api/reports/top')->assertOk()->json();
        $this->assertArrayHasKey('top_clients', $top);
        $this->assertArrayHasKey('top_products', $top);
        $this->assertArrayHasKey('top_services', $top);
    }
}
