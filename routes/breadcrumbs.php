<?php

use Saucebase\Breadcrumbs\Breadcrumbs;
use Saucebase\Breadcrumbs\Generator as Trail;

// Dashboard (home)
Breadcrumbs::for('dashboard', function (Trail $trail) {
    $trail->push('dashboard', route('dashboard'));
});
