<?php

namespace Modules\Themes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemesControllerTest extends TestCase
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
        // Clean up any test themes saved to storage
        if (is_dir($this->storageDir)) {
            foreach (glob($this->storageDir.'/test-*.json') ?: [] as $file) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    private function validPayload(string $name = 'test-theme'): array
    {
        return [
            'name' => $name,
            'title' => 'Test Theme',
            'description' => 'A test theme',
            'cssVars' => [
                'theme' => ['font-sans' => 'Inter, sans-serif'],
                'light' => ['primary' => 'oklch(0.5 0.2 200)', 'background' => 'oklch(1 0 0)'],
                'dark' => ['primary' => 'oklch(0.4 0.2 200)', 'background' => 'oklch(0.2 0 0)'],
            ],
        ];
    }

    public function test_valid_new_format_saves_theme_and_returns_success(): void
    {
        $response = $this->postJson(route('themes.store'), $this->validPayload());

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertFileExists($this->storageDir.'/test-theme.json');
    }

    public function test_saved_file_uses_new_shadcn_format(): void
    {
        $this->postJson(route('themes.store'), $this->validPayload());

        $saved = json_decode(file_get_contents($this->storageDir.'/test-theme.json'), true);

        $this->assertSame('test-theme', $saved['name']);
        $this->assertSame('Test Theme', $saved['title']);
        $this->assertArrayHasKey('cssVars', $saved);
        $this->assertArrayHasKey('light', $saved['cssVars']);
        $this->assertArrayHasKey('dark', $saved['cssVars']);
    }

    public function test_duplicate_name_returns_422_with_error(): void
    {
        // Save once
        $this->postJson(route('themes.store'), $this->validPayload());

        // Try to save again with same name
        $response = $this->postJson(route('themes.store'), $this->validPayload());

        $response->assertStatus(422)->assertJsonPath('errors.name', __('A theme with this name already exists.'));
    }

    public function test_name_must_be_lowercase_kebab_case(): void
    {
        $response = $this->postJson(route('themes.store'), [
            ...$this->validPayload(),
            'name' => 'My Theme With Spaces',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('name');
    }

    public function test_title_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['title']);

        $response = $this->postJson(route('themes.store'), $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('title');
    }

    public function test_css_vars_light_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['cssVars']['light']);

        $response = $this->postJson(route('themes.store'), $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('cssVars.light');
    }

    public function test_css_vars_dark_is_required(): void
    {
        $payload = $this->validPayload();
        unset($payload['cssVars']['dark']);

        $response = $this->postJson(route('themes.store'), $payload);

        $response->assertUnprocessable()->assertJsonValidationErrors('cssVars.dark');
    }

    public function test_css_vars_theme_is_optional(): void
    {
        $payload = $this->validPayload();
        unset($payload['cssVars']['theme']);

        $response = $this->postJson(route('themes.store'), $payload);

        $response->assertOk()->assertJson(['success' => true]);
    }

    public function test_shipped_theme_name_is_rejected_as_duplicate(): void
    {
        // 'default' is a shipped theme that already exists in the module
        $response = $this->postJson(route('themes.store'), [
            ...$this->validPayload(),
            'name' => 'default',
        ]);

        $response->assertStatus(422)->assertJsonPath('errors.name', __('A theme with this name already exists.'));
    }
}
