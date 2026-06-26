<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('registers a user and returns a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);
    $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
});

it('doesnt allow registering the same email twice', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/register', [
        'name' => 'Bob',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertStatus(422);
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['user' => ['id'], 'token']);
});

it('rejects login with a wrong password', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertStatus(422);
});

it('returns the current user from /me', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/me')->assertOk()->assertJsonPath('data.email', $user->email);
});

it('logout revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)->postJson('/api/logout')->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});
