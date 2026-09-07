<?php

namespace App\Settings;

/**
 * One section of the settings modal.
 *
 * A module contributes a section by putting a subclass in its own `src/Settings`
 * directory; {@see SectionRegistry} discovers it there, the same way Spatie's
 * settings classes are discovered from that directory. There is nothing to
 * register and nothing to remember to unregister — deleting a module takes its
 * sections with it.
 *
 * Sections are resolved through the container, so declare what you need in the
 * constructor rather than reaching for helpers inside {@see props()}.
 */
abstract class SettingsSection
{
    /**
     * Address of a section, for redirecting somebody into it.
     *
     * The fragment never reaches the server, so this is a normal page URL with the
     * modal's own state hung off the end of it.
     */
    public static function url(string $slug): string
    {
        return route('dashboard').'#settings/'.$slug;
    }

    /** Identifies the section in the `#settings/<slug>` fragment. */
    abstract public function slug(): string;

    /** Shown in the modal's sidebar. Translate here, not in the registry. */
    abstract public function title(): string;

    /** The front-end component the panel renders, e.g. `Billing::SettingsBilling`. */
    abstract public function component(): string;

    /**
     * The panel's data.
     *
     * Only the section being shown is called; the rest are wrapped in
     * `Inertia::optional()` and resolved when the user switches to them.
     *
     * @return array<string, mixed>
     */
    abstract public function props(): array;

    /** Registered icon name for the sidebar entry. */
    public function icon(): ?string
    {
        return null;
    }

    /** Lower sorts first. Leaves room between the defaults for later sections. */
    public function order(): int
    {
        return 100;
    }

    /**
     * Whether this section applies to the current request.
     *
     * Evaluated per request, so it can depend on the signed-in user or the host
     * being served — a workspace section has nothing to show on a central host.
     */
    public function visible(): bool
    {
        return true;
    }
}
