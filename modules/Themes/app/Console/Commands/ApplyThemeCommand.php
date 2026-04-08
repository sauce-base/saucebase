<?php

namespace Modules\Themes\Console\Commands;

use Illuminate\Console\Command;

class ApplyThemeCommand extends Command
{
    protected $signature = 'saucebase:theme:apply {theme : The theme ID to apply (e.g. blueberry)}';

    protected $description = 'Apply a theme by patching its variables into theme.css';

    public function handle(): int
    {
        $themeId = $this->argument('theme');
        $jsonPath = module_path('Themes', "resources/themes/{$themeId}.json");

        if (! file_exists($jsonPath)) {
            $this->error("Theme '{$themeId}' not found. Expected: {$jsonPath}");

            return self::FAILURE;
        }

        $content = file_get_contents($jsonPath);
        if ($content === false) {
            $this->error("Could not read theme file: {$jsonPath}");

            return self::FAILURE;
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($content, true);

        if (! is_array($data)) {
            $this->error("Invalid theme file format: {$jsonPath}");

            return self::FAILURE;
        }

        if (! isset($data['cssVars'])) {
            $this->error("Invalid theme file format: {$jsonPath}");

            return self::FAILURE;
        }

        /** @param array<string, string> $vars */
        $prefixKeys = static function (array $vars): array {
            $result = [];
            foreach ($vars as $key => $value) {
                $result['--'.$key] = $value;
            }

            return $result;
        };

        /** @var array<string, string> $themeVars */
        $themeVars = isset($data['cssVars']['theme']) && is_array($data['cssVars']['theme'])
            ? $prefixKeys($data['cssVars']['theme'])
            : [];

        /** @var array<string, string> $lightVars */
        $lightVars = isset($data['cssVars']['light']) && is_array($data['cssVars']['light'])
            ? $prefixKeys($data['cssVars']['light'])
            : [];

        /** @var array<string, string> $darkVars */
        $darkVars = isset($data['cssVars']['dark']) && is_array($data['cssVars']['dark'])
            ? $prefixKeys($data['cssVars']['dark'])
            : [];

        $light = array_merge($themeVars, $lightVars);
        $dark = $darkVars; // theme vars belong in :root only, not .dark

        if (empty($light) && empty($dark)) {
            $this->error("Invalid theme file format: {$jsonPath}");

            return self::FAILURE;
        }

        $themeCssPath = resource_path('css/theme.css');

        if (! file_exists($themeCssPath)) {
            $this->error("theme.css not found at: {$themeCssPath}");

            return self::FAILURE;
        }

        $css = file_get_contents($themeCssPath);
        if ($css === false) {
            $this->error('Could not read theme.css');

            return self::FAILURE;
        }

        if (! empty($light)) {
            $css = $this->patchBlock($css, ':root', $light);
        }

        if (! empty($dark)) {
            $css = $this->patchBlock($css, '.dark', $dark);
        }

        /** @var array<string, array<string, string>> $layerBase */
        $layerBase = isset($data['css']['@layer base']) && is_array($data['css']['@layer base'])
            ? $data['css']['@layer base']
            : [];

        if (! empty($layerBase)) {
            $css = $this->patchLayerBase($css, $this->renderLayerBase($layerBase));
        }

        file_put_contents($themeCssPath, $css);

        $lightCount = count($light);
        $darkCount = count($dark);
        $this->info("Theme '{$themeId}' applied: {$lightCount} light vars, {$darkCount} dark vars patched.");
        $this->line('Run <comment>npm run build</comment> to compile the changes.');

        return self::SUCCESS;
    }

    /**
     * Patch specific CSS variables inside a top-level selector block (e.g. :root or .dark).
     * Existing vars are updated in-place; new vars are appended before the closing brace.
     * Note: [^{}]* intentionally rejects nested braces — theme.css :root/.dark blocks are flat.
     *
     * @param  array<string, string>  $vars
     */
    private function patchBlock(string $css, string $selector, array $vars): string
    {
        $pattern = '/'.preg_quote($selector, '/').'\s*\{([^{}]*)\}/s';

        return preg_replace_callback($pattern, function (array $matches) use ($vars): string {
            $block = $matches[1];

            foreach ($vars as $variable => $value) {
                $varPattern = '/'.preg_quote($variable, '/').'\s*:[^;]+;/';
                $replacement = "{$variable}: {$value};";

                if (preg_match($varPattern, $block)) {
                    $block = (string) preg_replace($varPattern, $replacement, $block);
                } else {
                    $block = rtrim($block)."\n    {$replacement}\n";
                }
            }

            return str_replace($matches[1], $block, $matches[0]);
        }, $css) ?? $css;
    }

    /**
     * @param  array<string, array<string, string>>  $rules
     */
    private function renderLayerBase(array $rules): string
    {
        $lines = [];
        foreach ($rules as $selector => $properties) {
            $lines[] = "    {$selector} {";
            foreach ($properties as $property => $value) {
                $lines[] = "        {$property}: {$value};";
            }
            $lines[] = '    }';
        }

        return implode("\n", $lines);
    }

    private function patchLayerBase(string $css, string $content): string
    {
        $pattern = '/@layer\s+base\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s';
        $block = "@layer base {\n{$content}\n}";

        if (preg_match($pattern, $css)) {
            return (string) preg_replace($pattern, $block, $css);
        }

        return rtrim($css)."\n\n{$block}\n";
    }
}
