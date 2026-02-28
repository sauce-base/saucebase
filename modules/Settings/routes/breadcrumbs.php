<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

// Settings index
Breadcrumbs::for('settings.index', function (Trail $trail) {
    $trail->parent('dashboard');
    $trail->push('settings.index', route('settings.index'));
});

// Profile view
Breadcrumbs::for('settings.profile', function (Trail $trail) {
    $trail->parent('settings.index');
    $trail->push('settings.profile', route('settings.profile'));
});

// Profile edit
Breadcrumbs::for('settings.profile.edit', function (Trail $trail) {
    $trail->parent('settings.profile');
    $trail->push('settings.profile.edit', route('settings.profile.edit'));
});
