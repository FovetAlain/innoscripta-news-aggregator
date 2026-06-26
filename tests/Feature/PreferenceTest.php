<?php

use App\Models\Category;
use App\Models\Source;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('requires authentication', function () {
    $this->getJson('/api/preferences')->assertUnauthorized();
});

it('returns empty prefs for a brand new user', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/preferences')
        ->assertOk()
        ->assertJsonPath('data.preferred_sources', [])
        ->assertJsonPath('data.preferred_categories', []);
});

it('updates prefs', function () {
    $user = User::factory()->create();
    $source = Source::factory()->create();
    $category = Category::factory()->create();
    Sanctum::actingAs($user);

    $this->putJson('/api/preferences', [
        'preferred_sources' => [$source->id],
        'preferred_categories' => [$category->id],
    ])
        ->assertOk()
        ->assertJsonPath('data.preferred_sources', [$source->id]);

    $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id]);
});

it('422 when a preferred source id doesnt exist', function () {
    Sanctum::actingAs(User::factory()->create());

    // 999 is never seeded here so the exists rule should bite
    $this->putJson('/api/preferences', ['preferred_sources' => [999]])
        ->assertStatus(422);
});
