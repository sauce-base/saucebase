<?php

use App\Providers\AppServiceProvider;
use App\Providers\BreadcrumbServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\MacroServiceProvider;
use App\Providers\NavigationServiceProvider;
use App\Providers\SettingsServiceProvider;

return [
    AppServiceProvider::class,
    BreadcrumbServiceProvider::class,
    AdminPanelProvider::class,
    MacroServiceProvider::class,
    NavigationServiceProvider::class,
    SettingsServiceProvider::class,
];
