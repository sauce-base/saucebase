<?php

namespace Modules\Settings\Providers;

use App\Providers\ModuleServiceProvider;

class SettingsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Settings';

    protected string $nameLower = 'settings';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
