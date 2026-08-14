<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Settings belongs at the bottom of the sidebar.
 *
 * It is where you go last and least often, and every module adds to it — so a settings
 * entry that drifts upward pushes the things people came for further down. Modules
 * register their navigation independently, so nothing but a test notices when one of
 * them changes the order.
 */
class AdminNavigationOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function navigationLabels(): array
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ADMIN);

        $this->actingAs($user);

        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        $labels = [];

        foreach (Filament::getNavigation() as $group) {
            $label = $group instanceof NavigationGroup ? $group->getLabel() : null;

            foreach ($group->getItems() as $item) {
                $labels[] = trim(($label ?? '').' > '.$item->getLabel(), ' >');
            }
        }

        return $labels;
    }

    public function test_settings_is_the_last_thing_in_the_sidebar(): void
    {
        $labels = $this->navigationLabels();

        $this->assertNotEmpty($labels);

        $settings = array_values(array_filter(
            $labels,
            fn (string $label): bool => str_contains($label, 'Settings'),
        ));

        $this->assertNotEmpty($settings, 'No settings entry found in: '.implode(' | ', $labels));

        $this->assertSame(
            end($settings),
            end($labels),
            'Settings should be last. Order was: '.implode(' | ', $labels),
        );
    }
}
