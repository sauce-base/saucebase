<?php

namespace Modules\Roadmap\Providers;

use App\Providers\ModuleServiceProvider;

class RoadmapServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Roadmap';

    protected string $nameLower = 'roadmap';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
