<?php

use Saucebase\Breadcrumbs\Breadcrumbs;
use Saucebase\Breadcrumbs\Generator as Trail;

// Billing settings
Breadcrumbs::for('settings.billing', function (Trail $trail) {
    $trail->parent('settings.index');
    $trail->push('settings.billing', route('settings.billing'));
});
