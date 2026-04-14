<?php

namespace Modules\Demo\Providers;

use App\Providers\ModuleServiceProvider;

class DemoServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Demo';

    protected string $nameLower = 'demo';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
