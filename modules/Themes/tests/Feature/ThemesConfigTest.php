<?php

namespace Modules\Themes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemesConfigTest extends TestCase
{
    use RefreshDatabase;

    private string $storageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageDir = storage_path('app/themes');
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storageDir)) {
            foreach (glob($this->storageDir.'/test-*.json') ?: [] as $file) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    private function validPayload(string $name = 'test-editable'): array
    {
        return [
            'name' => $name,
            'title' => 'Test Editable',
            'description' => '',
            'cssVars' => [
                'theme' => [],
                'light' => ['primary' => 'oklch(0.5 0.2 200)'],
                'dark' => ['primary' => 'oklch(0.4 0.2 200)'],
            ],
        ];
    }

    private function createUserTheme(string $name = 'test-editable'): void
    {
        if (! is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        file_put_contents(
            $this->storageDir."/{$name}.json",
            json_encode($this->validPayload($name), JSON_PRETTY_PRINT)
        );
    }

    // ── themes.enabled ────────────────────────────────────────────────────────

    public function test_when_disabled_themes_prop_is_null(): void
    {
        config(['themes.enabled' => false]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page->where('themes', null));
    }

    public function test_when_enabled_themes_prop_has_items(): void
    {
        config(['themes.enabled' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('themes')
            ->has('themes.items.0')
        );
    }

    public function test_themes_include_editable_flag(): void
    {
        config(['themes.enabled' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('themes.items.0')
            ->has('themes.items.0.editable')
        );
    }

    public function test_shipped_themes_have_editable_false(): void
    {
        config(['themes.enabled' => true]);

        $response = $this->get('/');

        // Default theme is always first (sorted to position 0)
        $response->assertInertia(fn ($page) => $page->where('themes.items.0.editable', false));
    }

    public function test_user_theme_has_editable_true(): void
    {
        config(['themes.enabled' => true]);
        $this->createUserTheme('test-editable');

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->where('themes.items', fn ($items) => $items->firstWhere('id', 'test-editable')['editable'] === true)
        );
    }

    public function test_fonts_are_shared_under_themes_key(): void
    {
        config(['themes.enabled' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn ($page) => $page
            ->has('themes.fonts.sans')
            ->has('themes.fonts.serif')
            ->has('themes.fonts.mono')
        );
    }

    // ── PUT /themes/{name} ────────────────────────────────────────────────────

    public function test_update_overwrites_user_theme(): void
    {
        $this->createUserTheme('test-editable');

        $payload = $this->validPayload();
        $payload['title'] = 'Updated Title';

        $response = $this->putJson(route('themes.update', ['name' => 'test-editable']), $payload);

        $response->assertOk()->assertJson(['success' => true]);

        $saved = json_decode(file_get_contents($this->storageDir.'/test-editable.json'), true);
        $this->assertSame('Updated Title', $saved['title']);
    }

    public function test_update_returns_404_for_non_existent_theme(): void
    {
        $response = $this->putJson(route('themes.update', ['name' => 'test-does-not-exist']), $this->validPayload('test-does-not-exist'));

        $response->assertStatus(404);
    }

    public function test_bundled_theme_cannot_be_updated(): void
    {
        // 'default' is a bundled/shipped theme — not in storage/app/themes/
        $response = $this->putJson(route('themes.update', ['name' => 'default']), [
            ...$this->validPayload(),
            'name' => 'default',
            'title' => 'Hacked Default',
        ]);

        $response->assertStatus(404);
    }

    public function test_update_validates_required_fields(): void
    {
        $this->createUserTheme('test-editable');

        $response = $this->putJson(route('themes.update', ['name' => 'test-editable']), [
            'name' => 'test-editable',
            // missing title, cssVars
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['title', 'cssVars']);
    }
}
