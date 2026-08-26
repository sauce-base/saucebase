<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SauceBase\RecipeToModuleCommand;
use Illuminate\Console\OutputStyle;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

class TestableRecipeToModuleCommand extends RecipeToModuleCommand
{
    public string $moduleName = '';

    public string $moduleFolder = '';

    public string $moduleConfigPath = '';

    public string $composerVendor = '';

    public string $composerType = '';

    public function setModuleNames(string $input): void
    {
        parent::setModuleNames($input);
    }

    public function placeholders(string $name): array
    {
        return parent::placeholders($name);
    }

    public function writeModuleEntryPoint(string $framework, bool $isDev): void
    {
        parent::writeModuleEntryPoint($framework, $isDev);
    }
}

class RecipeToModuleCommandTest extends TestCase
{
    private TestableRecipeToModuleCommand $command;

    private Filesystem $files;

    private string $tmpDir;

    private string $originalBasePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new TestableRecipeToModuleCommand;

        $this->files = new Filesystem;
        $this->tmpDir = sys_get_temp_dir().'/saucebase-recipe-test-'.uniqid();
        $jsRoot = $this->tmpDir.'/modules/test-module/resources/js';
        $this->files->makeDirectory($jsRoot, 0755, true);
        file_put_contents($jsRoot.'/app.ts', "export * from './vue/app';\n");

        exec("git -C {$this->tmpDir} init -q 2>/dev/null");
        exec("git -C {$this->tmpDir} config user.email 'test@test.com' 2>/dev/null");
        exec("git -C {$this->tmpDir} config user.name 'Test' 2>/dev/null");

        $this->originalBasePath = base_path();
        app()->setBasePath($this->tmpDir);
    }

    protected function tearDown(): void
    {
        app()->setBasePath($this->originalBasePath);
        $this->files->deleteDirectory($this->tmpDir);
        parent::tearDown();
    }

    // ── Name normalization ────────────────────────────────────────────────────

    #[DataProvider('moduleNameInputProvider')]
    public function test_set_module_names_normalizes_input(string $input, string $expectedName, string $expectedFolder): void
    {
        $this->command->setModuleNames($input);

        $this->assertSame($expectedName, $this->command->moduleName);
        $this->assertSame($expectedFolder, $this->command->moduleFolder);
    }

    public static function moduleNameInputProvider(): array
    {
        return [
            'kebab-case input' => ['invoice-test', 'InvoiceTest', 'invoice-test'],
            'StudlyCase input' => ['InvoiceTest', 'InvoiceTest', 'invoice-test'],
            'single word kebab' => ['auth', 'Auth', 'auth'],
            'single word studly' => ['Auth', 'Auth', 'auth'],
            'multi-word kebab' => ['super-admin-panel', 'SuperAdminPanel', 'super-admin-panel'],
            'multi-word studly' => ['SuperAdminPanel', 'SuperAdminPanel', 'super-admin-panel'],
        ];
    }

    // ── Placeholder map ───────────────────────────────────────────────────────

    public function test_placeholders_module_base_variants(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['{Module}']);
        $this->assertSame('invoicetest', $map['{module}']);
        $this->assertSame('Invoice_Test', $map['{Module_}']);
        $this->assertSame('invoice_test', $map['{module_}']);
        $this->assertSame('Invoice-Test', $map['{Module-}']);
        $this->assertSame('invoice-test', $map['{module-}']);
    }

    public function test_placeholders_module_camel_and_studly(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('invoiceTest', $map['{moduleCamel}']);
        $this->assertSame('InvoiceTest', $map['{ModuleStudly}']);
        $this->assertSame('InvoiceTest', $map['{moduleStudly}']);
    }

    public function test_placeholders_module_human_readable(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('Invoice Test', $map['{Module }']);
        $this->assertSame('invoice test', $map['{module }']);
        $this->assertSame('Invoice Test', $map['{ModuleTitle}']);
        $this->assertSame('Invoice Test', $map['{module_title}']);
    }

    public function test_placeholders_module_plural(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('invoice tests', $map['{module_plural}']);
        $this->assertSame('invoice_tests', $map['{module_plural_snake}']);
        $this->assertSame('invoice-tests', $map['{module_plural_kebab}']);
    }

    public function test_placeholders_model_variants(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['{Model}']);
        $this->assertSame('invoicetest', $map['{model}']);
        $this->assertSame('invoice-test', $map['{model-}']);
        $this->assertSame('invoice_test', $map['{model_}']);
    }

    public function test_placeholders_js_friendly_tokens(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('InvoiceTest', $map['___Module___']);
        $this->assertSame('invoicetest', $map['___module___']);
    }

    public function test_placeholders_namespace_tokens(): void
    {
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('Modules', $map['{MODULE_NAMESPACE}']);
        $this->assertSame('Modules', $map['___MODULE_NAMESPACE___']);
    }

    public function test_placeholders_composer_vendor(): void
    {
        $this->command->composerVendor = 'saucebase';
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('saucebase', $map['{COMPOSER_VENDOR}']);
    }

    public function test_placeholders_composer_type(): void
    {
        $this->command->composerType = 'library';
        $map = $this->command->placeholders('InvoiceTest');

        $this->assertSame('library', $map['{COMPOSER_TYPE}']);
    }

    public function test_placeholders_single_word_module(): void
    {
        $map = $this->command->placeholders('Auth');

        $this->assertSame('Auth', $map['{Module}']);
        $this->assertSame('auth', $map['{module}']);
        $this->assertSame('auth', $map['{module-}']);
        $this->assertSame('auth', $map['{module_}']);
        $this->assertSame('Auth', $map['___Module___']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function writeFrontendJson(array $data): void
    {
        file_put_contents($this->tmpDir.'/frontend.json', json_encode($data));
    }

    private function makeCommand(): TestableRecipeToModuleCommand
    {
        $cmd = new TestableRecipeToModuleCommand;
        $cmd->moduleConfigPath = $this->tmpDir.'/modules/';
        $cmd->moduleFolder = 'test-module';
        $cmd->setOutput(new OutputStyle(new ArrayInput([]), new NullOutput));

        return $cmd;
    }

    private function jsRoot(): string
    {
        return $this->tmpDir.'/modules/test-module/resources/js';
    }

    // ── Frontend framework: early exit ────────────────────────────────────────

    public function test_aborts_when_no_framework_is_selected(): void
    {
        $this->writeFrontendJson(['framework' => null]);

        $this->artisan('saucebase:recipe', ['module' => 'TestExit'])
            ->expectsPromptsError('No frontend framework selected. Run `saucebase stack vue` or `saucebase stack react` first.');

        $this->assertDirectoryDoesNotExist($this->tmpDir.'/modules/test-exit');
    }

    // ── writeModuleEntryPoint: install mode ───────────────────────────────────

    public function test_install_mode_vue_keeps_app_ts(): void
    {
        $this->makeCommand()->writeModuleEntryPoint('vue', false);

        $this->assertFileExists($this->jsRoot().'/app.ts');
    }

    public function test_install_mode_react_removes_stale_app_ts(): void
    {
        $this->makeCommand()->writeModuleEntryPoint('react', false);

        $this->assertFileDoesNotExist($this->jsRoot().'/app.ts');
    }

    // ── writeModuleEntryPoint: dev mode ───────────────────────────────────────

    public function test_dev_mode_react_writes_proxy(): void
    {
        $this->files->makeDirectory($this->jsRoot().'/react', 0755, true);

        $this->makeCommand()->writeModuleEntryPoint('react', true);

        $this->assertStringContainsString(
            "export * from './react/app'",
            (string) file_get_contents($this->jsRoot().'/app.ts')
        );
    }

    public function test_dev_mode_vue_writes_proxy(): void
    {
        $this->files->makeDirectory($this->jsRoot().'/vue', 0755, true);

        $this->makeCommand()->writeModuleEntryPoint('vue', true);

        $this->assertStringContainsString(
            "export * from './vue/app'",
            (string) file_get_contents($this->jsRoot().'/app.ts')
        );
    }
}
