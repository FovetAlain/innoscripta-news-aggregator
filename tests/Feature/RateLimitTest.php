<?php

it('exposes rate limit headers on api responses', function () {
    $this->getJson('/api/articles')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', 60);
});

it('throttles repeated login attempts', function () {
    $payload = ['email' => 'attacker@example.com', 'password' => 'guess'];

    // The 'auth' limiter allows 5 hits per minute; the 6th is blocked.
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', $payload)->assertStatus(422);
    }

    $this->postJson('/api/login', $payload)->assertStatus(429);
});
