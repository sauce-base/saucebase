<?php

namespace Modules\Blog\Providers;

use App\Providers\ModuleServiceProvider;

class BlogServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Blog';

    protected string $nameLower = 'blog';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
