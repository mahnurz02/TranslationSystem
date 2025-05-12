<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Translation;

class TranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_store_translation_successfully()
    {
        $this->authenticate();

        $payload = [
            'key' => 'greeting',
            'locale' => 'en',
            'value' => 'Hello',
            'context' => 'homepage',
        ];

        $response = $this->postJson('/api/translations', $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['key' => 'greeting', 'locale' => 'en']);
    }

    public function test_store_translation_validation_error()
    {
        $this->authenticate();

        $response = $this->postJson('/api/translations', []); // Missing fields

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['key', 'locale', 'value', 'context']);
    }

    public function test_index_returns_paginated_translations()
    {
        $this->authenticate();

        Translation::factory()->count(5)->create(['locale' => 'en']);

        $response = $this->getJson('/api/translations/en');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_destroy_translation()
    {
        $this->authenticate();

        $translation = Translation::factory()->create();

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Deleted']);

        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_search_translation()
    {
        $this->authenticate();

        Translation::factory()->create([
            'key' => 'welcome',
            'locale' => 'en',
            'context' => 'home'
        ]);

        $response = $this->getJson('/api/translations/search?key=welcome');

        $response->assertStatus(200)
                 ->assertJsonFragment(['key' => 'welcome']);
    }

    public function test_export_translations()
    {
        $this->authenticate();

        Translation::factory()->create([
            'key' => 'bye',
            'locale' => 'en',
            'value' => 'Goodbye'
        ]);

        $response = $this->getJson('/api/translations/export?locale=en');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'pagination']);
    }
}