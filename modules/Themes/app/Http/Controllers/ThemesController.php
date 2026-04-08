<?php

namespace Modules\Themes\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class ThemesController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-z0-9-]+$/'],
            'title' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'cssVars' => ['required', 'array'],
            'cssVars.light' => ['required', 'array'],
            'cssVars.dark' => ['required', 'array'],
            'cssVars.theme' => ['nullable', 'array'],
        ]);

        $dir = storage_path('app/themes');
        $path = $this->themePath($validated['name']);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($this->themeExists($validated['name'])) {
            return response()->json(['errors' => ['name' => __('A theme with this name already exists.')]], 422);
        }

        file_put_contents($path, json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['success' => true]);
    }

    public function update(Request $request, string $name): JsonResponse
    {
        $nameValidator = Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'regex:/^[a-z0-9-]+$/']]
        );

        if ($nameValidator->fails()) {
            return response()->json(['errors' => $nameValidator->errors()], 422);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'regex:/^[a-z0-9-]+$/'],
            'title' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'cssVars' => ['required', 'array'],
            'cssVars.light' => ['required', 'array'],
            'cssVars.dark' => ['required', 'array'],
            'cssVars.theme' => ['nullable', 'array'],
        ]);

        $path = $this->themePath($name);

        if (! file_exists($path)) {
            return response()->json(['errors' => ['name' => __('Theme not found or is not editable.')]], 404);
        }

        file_put_contents($path, json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['success' => true]);
    }

    public function destroy(string $name): JsonResponse
    {
        $validator = Validator::make(
            ['name' => $name],
            ['name' => ['required', 'string', 'regex:/^[a-z0-9-]+$/']]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = $this->themePath($name);

        if (! file_exists($path)) {
            return response()->json(['errors' => ['name' => __('Theme not found.')]], 404);
        }

        unlink($path);

        return response()->json(['success' => true]);
    }

    private function themePath(string $name): string
    {
        return storage_path("app/themes/{$name}.json");
    }

    private function themeExists(string $name): bool
    {
        return file_exists($this->themePath($name))
            || file_exists(module_path('Themes', "resources/themes/{$name}.json"));
    }
}
