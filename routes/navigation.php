<?php

use App\Facades\Navigation;
use App\Navigation\Section;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Core Navigation
|--------------------------------------------------------------------------
|
| Define core application navigation items here.
| These items will be loaded automatically by the Navigation service.
|
*/

Navigation::add('Dashboard', fn () => route('dashboard'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'dashboard',
        'icon' => 'dashboard',
        'order' => 0,
    ]);
});

Navigation::add(
    'Star us on Github',
    'https://github.com/saucebase-dev/saucebase',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'github',
            'icon' => 'github',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::add(
    'Documentation',
    'https://saucebase-dev.github.io/docs/',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'documentation',
            'icon' => 'documentation',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::addWhen(
    fn () => Auth::check() && Auth::user()->isAdmin(),
    'Admin',
    fn () => route('filament.admin.pages.dashboard'),
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'admin',
            'icon' => 'admin',
            'order' => 10,
            'external' => true,
            'newPage' => true,
            'class' => 'bg-yellow-500/10 text-yellow-600 hover:bg-yellow-500/20 hover:text-yellow-700 dark:hover:text-yellow-400',
        ]);
    }
);
