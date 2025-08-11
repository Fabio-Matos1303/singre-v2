<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_auth(): void
    {
        $this->getJson('/api/settings')->assertStatus(401);
    }

    public function test_crud_settings(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // create
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->postJson('/api/settings', ['key'=>'company.name','value'=>'Acme Inc'])
          ->assertStatus(201);

        $this->assertEquals('Acme Inc', Setting::getValue('company.name'));

        // list
        $list = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->getJson('/api/settings')->assertOk()->json();
        $this->assertTrue(collect($list)->contains(fn ($s) => $s['key'] === 'company.name'));

        // update (by id)
        $setting = Setting::query()->where('key','company.name')->first();
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->putJson('/api/settings/'.$setting->id, ['value'=>'Acme BR'])
          ->assertOk();
        $this->assertEquals('Acme BR', Setting::getValue('company.name'));

        // delete
        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->deleteJson('/api/settings/'.$setting->id)
          ->assertOk();
        $this->assertNull(Setting::query()->where('key','company.name')->first());
    }
}
