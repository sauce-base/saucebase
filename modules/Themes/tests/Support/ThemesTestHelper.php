<?php

namespace Modules\Themes\Tests\Support;

class ThemesTestHelper
{
    public static function cleanUserThemes(): void
    {
        $dir = storage_path('app/themes');

        if (! is_dir($dir)) {
            return;
        }

        foreach (glob("{$dir}/*.json") as $file) {
            unlink($file);
        }
    }

    public static function setEnabled(bool $enabled): void
    {
        config(['themes.enabled' => $enabled]);
    }

    public static function resetConfig(): void
    {
        config([
            'themes.enabled' => true,
        ]);
    }
}
