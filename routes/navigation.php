<?php

use Illuminate\Support\Facades\Auth;
use App\Facades\Navigation;
use Spatie\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Core Navigation
|--------------------------------------------------------------------------
|
| Define core application navigation items here.
| These items will be loaded automatically by the Navigation service.
|
*/

Navigation::add('Dashboard', route('dashboard'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'dashboard',
        'order' => 0,
    ]);
});

Navigation::add(
    'Star us on Github',
    'https://github.com/sauce-base/saucebase',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'github',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::add(
    'Documentation',
    'https://sauce-base.github.io/docs/',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'documentation',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::addWhen(
    fn () => Auth::check() && Auth::user()->isAdmin(),
    'Admin',
    route('filament.admin.pages.dashboard'),
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'admin',
            'order' => 10,
            'external' => true,
            'newPage' => true,
            'class' => 'bg-yellow-500/10 text-yellow-600 hover:bg-yellow-500/20 hover:text-yellow-700 dark:hover:text-yellow-400',
        ]);
    }
);

/*
|--------------------------------------------------------------------------
| Landing Page Navigation
|--------------------------------------------------------------------------
|
| Anchor links for the landing page. These use 'external' => true to
| render as regular <a> tags (Inertia Link doesn't handle anchors).
|
*/

Navigation::add('Features', '/#features', function (Section $section) {
    $section->attributes([
        'group' => 'landing',
        'slug' => 'features',
        'external' => true,
        'order' => 0,
    ]);
});

Navigation::add('FAQ', '/#faq', function (Section $section) {
    $section->attributes([
        'group' => 'landing',
        'slug' => 'faq',
        'external' => true,
        'order' => 1,
    ]);
});

Navigation::add(
    'Docs',
    'https://sauce-base.github.io/docs/',
    function (Section $section) {
        $section->attributes([
            'group' => 'landing',
            'slug' => 'documentation',
            'external' => true,
            'newPage' => true,
            'order' => 2,
        ]);
    }
);
