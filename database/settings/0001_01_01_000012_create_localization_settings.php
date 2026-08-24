<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Seeded from config so an existing installation keeps the languages it had.
        if (! $this->migrator->exists('localization.enabled_locales')) {
            $this->migrator->add(
                'localization.enabled_locales',
                array_keys(config('app.available_locales', ['en' => 'English'])),
            );
        }

        if (! $this->migrator->exists('localization.default_locale')) {
            $this->migrator->add('localization.default_locale', config('app.locale', 'en'));
        }
    }
};
