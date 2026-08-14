<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Admin\Pages\GeneralSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_filament_builds_the_settings_navigation_group_from_page_metadata(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        $panel = Filament::getPanel('admin');

        Filament::setCurrentPanel($panel);
        Filament::bootCurrentPanel();

        $registeredSettingsGroup = collect($panel->getNavigationGroups())
            ->contains(fn (NavigationGroup|string $group): bool => $group instanceof NavigationGroup
                ? $group->getLabel() === __('Settings')
                : $group === __('Settings'));

        $navigation = collect(Filament::getNavigation())->values();

        $settingsGroup = $navigation
            ->first(fn (NavigationGroup $group): bool => $group->getLabel() === __('Settings'));

        $this->assertFalse($registeredSettingsGroup);
        $this->assertInstanceOf(NavigationGroup::class, $settingsGroup);
        $this->assertContains(
            __('General'),
            collect($settingsGroup->getItems())->map->getLabel()->all(),
        );
    }

    public function test_settings_pages_do_not_render_duplicate_sub_navigation(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::ADMIN);

        $this->actingAs($admin);

        $page = app(GeneralSettings::class);

        $this->assertSame([], $page->getSubNavigation());
    }
}
