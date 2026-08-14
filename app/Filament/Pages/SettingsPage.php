<?php

namespace App\Filament\Pages;

use Filament\Pages\SettingsPage as BaseSettingsPage;
use Filament\Support\Enums\Width;

/**
 * The base every module's settings page extends.
 *
 * Extending it gives module settings pages a consistent width and lets Filament place
 * them in the same navigation group without maintaining a central page list.
 */
abstract class SettingsPage extends BaseSettingsPage
{
    private const int MAX_NAVIGATION_SORT = 1000;

    private const int NAVIGATION_SORT_OFFSET = PHP_INT_MAX - self::MAX_NAVIGATION_SORT;

    /**
     * Filament pages fill the viewport, which on a wide monitor stretches a single-column
     * form across the whole screen and leaves labels far from their inputs.
     */
    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationSort(): ?int
    {
        return self::NAVIGATION_SORT_OFFSET + min(static::$navigationSort ?? 0, self::MAX_NAVIGATION_SORT);
    }
}
