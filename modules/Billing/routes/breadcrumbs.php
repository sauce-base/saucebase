<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

// Billing settings
Breadcrumbs::for('settings.billing', function (Trail $trail) {
    $trail->parent('settings.index');
    $trail->push('settings.billing', route('settings.billing'));
});
