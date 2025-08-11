<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_services_requires_auth(): void
    {
        $this->getJson('/api/services')->assertStatus(401);
    }

    public function test_can_create_service_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $payload = [
            'name' => 'Serviço Teste',
            'price' => 80,
        ];

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->postJson('/api/services', $payload)->assertStatus(201)->assertJsonFragment([
            'name' => 'Serviço Teste',
        ]);
    }
}
