<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Settings;

/**
 * Who the application says it is.
 *
 * Every brand-rendering surface resolves this — the logo, the sidebar, the page title,
 * the favicon — so anything that decorates this object rebrands all of them at once.
 */
class GeneralSettings extends Settings
{
    /** What the application calls itself, suffixed onto every page title. */
    public string $site_name;

    /** A short line under the name, on the pages that show one. */
    public ?string $site_tagline;

    /** The longer blurb, used as the meta description. */
    public ?string $site_description;

    /** The square mark, for where only a mark fits — a collapsed sidebar, a tab icon. */
    public ?string $site_icon;

    /** The wide wordmark, which names the application in place of the text beside it. */
    public ?string $site_logo;

    /**
     * Prefer the wordmark wherever either image would do.
     *
     * Off by default: with both uploaded, tight spaces keep the icon, where a wordmark
     * would be illegible.
     */
    public bool $prefer_logo;

    public function siteIconUrl(): ?string
    {
        return $this->publicFileUrl($this->site_icon);
    }

    public function siteLogoUrl(): ?string
    {
        return $this->publicFileUrl($this->site_logo);
    }

    public static function group(): string
    {
        return 'general';
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, '/') || Str::isUrl($path)
            ? $path
            : Storage::disk('public')->url($path);
    }
}
