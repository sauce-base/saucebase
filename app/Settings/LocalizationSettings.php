<?php

namespace App\Settings;

use Illuminate\Support\Str;
use Locale;
use Spatie\LaravelSettings\Settings;

/**
 * Which languages the application offers, and which one it speaks by default.
 *
 * Three things decide what appears in the language selector, and each is the only honest
 * source for its own part of the answer:
 *
 * - The filesystem decides which locales *exist*. A locale is real only if translation
 *   files shipped for it, so {@see available()} looks for them rather than trusting a list.
 * - `config('app.available_locales')` decides what each one is *called*.
 * - This object decides which of them the admin *offers*.
 *
 * Deliberately separate from {@see GeneralSettings}: the tenancy module clones and
 * decorates that object per tenant to rebrand it, and adding locales there would make
 * them per-workspace by accident. Locale is not branding.
 */
class LocalizationSettings extends Settings
{
    /** @var list<string> */
    public array $enabled_locales;

    public string $default_locale;

    public static function group(): string
    {
        return 'localization';
    }

    /**
     * Every locale with translation files behind it, mapped to its display name.
     *
     * Discovered rather than configured, so dropping `lang/es/` into the application — or
     * installing a module that ships one — offers Spanish without a code change.
     *
     * @return array<string, string>
     */
    public function available(): array
    {
        $paths = [
            ...glob(lang_path('*'), GLOB_ONLYDIR) ?: [],
            ...glob(base_path('modules/*/lang/*'), GLOB_ONLYDIR) ?: [],
        ];

        $locales = [];

        foreach (array_unique(array_map('basename', $paths)) as $code) {
            // `lang/vendor/` holds published package translations grouped by package, not
            // a locale of its own.
            if ($code === 'vendor') {
                continue;
            }

            $locales[$code] = $this->displayName($code);
        }

        asort($locales);

        return $locales;
    }

    /**
     * The subset the admin offers, mapped to display names.
     *
     * Never empty: an application with no language at all would render raw translation
     * keys, so a setting that resolves to nothing falls back to the configured locale.
     *
     * @return array<string, string>
     */
    public function enabled(): array
    {
        $available = $this->available();

        $enabled = array_intersect_key($available, array_flip($this->enabled_locales));

        if ($enabled !== []) {
            return $enabled;
        }

        $fallback = (string) config('app.locale', 'en');

        return [$fallback => $available[$fallback] ?? $this->displayName($fallback)];
    }

    /**
     * What a locale is called.
     *
     * Config first, because a language selector names a language in that language
     * ("Português", not "Portuguese (Brazil)") and intl does not produce that phrasing.
     * intl is only a fallback for a locale nobody has named, and `ext-intl` is not a
     * declared requirement, so its absence has to be survivable.
     */
    private function displayName(string $code): string
    {
        $configured = config('app.available_locales', [])[$code] ?? null;

        if (is_string($configured)) {
            return $configured;
        }

        if (! extension_loaded('intl')) {
            return $code;
        }

        return Str::ucfirst(Locale::getDisplayName($code, $code) ?: $code);
    }
}
