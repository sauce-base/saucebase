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
    }
};
