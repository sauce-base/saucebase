<?php

if (! function_exists('module_path')) {
    function module_path(string $name, string $path = ''): string
    {
        $modulesDir = config('app-modules.modules_directory', 'modules');

        return base_path($modulesDir.'/'.strtolower($name).($path ? '/'.ltrim($path, '/') : ''));
    }
}
