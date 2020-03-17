<?php

namespace Tests\Feature;

use App\User;
use Laravel\Airlock\Airlock;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @var \App\User */
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function authenticate()
    {
        $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['token'])
        ->assertJson(['token_type' => 'bearer']);
    }

    /** @test */
    public function fetch_the_current_user()
    {
        Airlock::actingAs($this->user);
            $this->getJson('/api/user')
            ->assertSuccessful()
            ->assertJsonStructure(['id', 'name', 'email']);
    }

    /** @test */
    public function log_out()
    {
        $token = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])->json()['token'];

        $headers = ['Authorization' => 'Bearer '.$token];

        $this->withHeaders($headers)->postJson("/api/logout")
            ->assertSuccessful();

        $this->refreshApplication();

        $this->flushHeaders()->getJson("/api/user")
            ->assertStatus(401);
    }
}
