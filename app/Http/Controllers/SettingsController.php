<?php

namespace App\Http\Controllers;

use App\Settings\SectionRegistry;
use App\Settings\SettingsSection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use InertiaUI\Modal\Modal;

class SettingsController extends Controller
{
    /**
     * Render the settings modal.
     *
     * The modal is opened from the `#settings/<section>` fragment rather than by
     * navigating here, so it can sit over whatever page the user is already on.
     * A fragment never reaches the server, so the front-end mirrors it onto a
     * `section` query parameter to say which panel it needs data for.
     */
    public function __invoke(Request $request, SectionRegistry $registry): Modal
    {
        $sections = $registry->all();
        $requested = $request->string('section')->toString();

        $active = $sections->first(
            fn (SettingsSection $section): bool => $section->slug() === $requested,
        ) ?? $sections->first();

        return Inertia::modal('Settings', [
            'activeSection' => $active?->slug(),
            'sectionProps' => $this->resolveProps($sections, $active),
        ]);
    }

    /**
     * Collect the props each section contributes.
     *
     * Only the section being shown is resolved eagerly; the rest stay optional, so
     * switching sections costs one partial reload rather than every section paying
     * for every other section's queries each time the modal opens.
     *
     * Matched on slug rather than object identity: sections are resolved fresh from
     * the container, so two lookups of the same section are two instances.
     *
     * @param  Collection<int, SettingsSection>  $sections
     * @return array<string, mixed>
     */
    private function resolveProps(Collection $sections, ?SettingsSection $active): array
    {
        return $sections
            ->mapWithKeys(fn (SettingsSection $section): array => [
                $section->slug() => $section->slug() === $active?->slug()
                    ? $section->props()
                    : Inertia::optional($section->props(...)),
            ])
            ->all();
    }
}
