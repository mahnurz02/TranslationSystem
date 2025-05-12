<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Translation;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationControllerUnitTest extends TestCase
{
public function test_store_creates_or_updates_translation()
    {
        $controller = new TranslationController();

        $request = new Request([
            'key' => 'welcome',
            'locale' => 'en',
            'value' => 'Welcome!',
            'context' => 'greeting',
        ]);

        $storeRequest = \Mockery::mock(\App\Http\Requests\StoreTranslationRequest::class);
        $storeRequest->shouldReceive('validated')->andReturn($request->all());

        Cache::shouldReceive('forget')->once()->with('translations_en');

        $response = $controller->store($storeRequest);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('translations', [
            'key' => 'welcome',
            'locale' => 'en',
            'value' => 'Welcome!',
        ]);
    }

    public function test_destroy_deletes_translation_and_forgets_cache()
    {
        $controller = new TranslationController();

        $translation = Translation::factory()->create([
            'locale' => 'en',
        ]);

        Cache::shouldReceive('forget')->once()->with("translations_en");

        $response = $controller->destroy($translation->id);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_export_returns_structured_json()
    {
        $controller = new TranslationController();

        Translation::factory()->create([
            'key' => 'goodbye',
            'value' => 'Bye',
            'locale' => 'en',
        ]);

        $request = new Request(['locale' => 'en']);

        $response = $controller->export($request);
        $json = $response->getData(true);

        $this->assertEquals('Bye', $json['data']['en']['goodbye']);
        $this->assertArrayHasKey('pagination', $json);
    }
}
