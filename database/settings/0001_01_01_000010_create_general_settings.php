<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('general.site_name')) {
            $this->migrator->add('general.site_name', config('app.name', 'Saucebase'));
        }

        if (! $this->migrator->exists('general.site_tagline')) {
            $this->migrator->add('general.site_tagline', null);
        }

        if (! $this->migrator->exists('general.site_description')) {
            $this->migrator->add('general.site_description', null);
        }

        if (! $this->migrator->exists('general.site_icon')) {
            $this->migrator->add('general.site_icon', null);
        }

        if (! $this->migrator->exists('general.site_logo')) {
            $this->migrator->add('general.site_logo', null);
        }

        if (! $this->migrator->exists('general.prefer_logo')) {
            $this->migrator->add('general.prefer_logo', false);
        }
    }
};
