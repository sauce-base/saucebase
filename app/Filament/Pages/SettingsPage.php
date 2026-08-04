<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\SettingsPage as BaseSettingsPage;
use Filament\Support\Enums\Width;

/**
 * The base every module's settings page extends.
 *
 * Two things come with extending it, and both exist so that adding a module's settings
 * page is one class rather than one class plus a list somebody has to remember to update.
 *
 * Keep this to presentation and navigation. Behaviour belongs on the page that needs it:
 * a shared base class is an easy place for one module's opinion to become everyone's.
 */
abstract class SettingsPage extends BaseSettingsPage
{
    /**
     * Filament pages fill the viewport, which on a wide monitor stretches a single-column
     * form across the whole screen and leaves labels far from their inputs. Wide enough to
     * sit beside the sub-navigation without either feeling cramped.
     */
    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    /**
     * The other settings pages, listed beside this one.
     *
     * Deliberately not a cluster. A cluster would give the same rail, but it also
     * replaces the sidebar entries with a single item of its own — and a cluster item
     * belongs to no navigation group, which puts it above every grouped item rather than
     * at the bottom where settings belong.
     *
     * Discovered from the panel rather than listed, so a module's page joins by extending
     * this class and nothing here has to know which modules are installed.
     *
     * @return array<NavigationItem>
     */
    public function getSubNavigation(): array
    {
        $pages = collect(Filament::getPages())
            ->filter(fn (string $page): bool => is_subclass_of($page, self::class))
            ->sortBy(fn (string $page): int => $page::getNavigationSort() ?? PHP_INT_MAX)
            ->values()
            ->all();

        return $this->generateNavigationItems($pages);
    }
}
