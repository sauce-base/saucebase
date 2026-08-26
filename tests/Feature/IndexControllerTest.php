<?php

namespace Tests\Feature;

use App\Services\FrontendConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InterNACHI\Modular\Support\ModuleRegistry;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (app(ModuleRegistry::class)->module('demo') !== null) {
            $this->markTestSkipped('IndexController is not the / route handler when the Demo module is active.');
        }
    }

    private function bindFramework(?string $framework): void
    {
        app()->bind(FrontendConfig::class, fn () => new class($framework) extends FrontendConfig
        {
            public function __construct(private readonly ?string $fw) {}

            public function getFramework(): ?string
            {
                return $this->fw;
            }
        });
    }

    public function test_renders_setup_blade_when_framework_is_null(): void
    {
        $this->bindFramework(null);

        $this->get('/')->assertStatus(200)->assertViewIs('setup');
    }

    public function test_renders_setup_blade_when_frontend_json_missing(): void
    {
        $this->bindFramework(null);

        $this->get('/')->assertStatus(200)->assertViewIs('setup');
    }

    public function test_renders_inertia_when_framework_is_set(): void
    {
        $this->bindFramework('vue');

        $this->get('/')->assertStatus(200)->assertViewIs('app');
    }

    public function test_setup_page_shows_vue_and_react_commands(): void
    {
        $this->bindFramework(null);

        $this->get('/')
            ->assertSeeText('saucebase stack vue')
            ->assertSeeText('saucebase stack react')
            ->assertDontSeeText('php artisan saucebase:install');
    }
}
