<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Demo Module Navigation
|--------------------------------------------------------------------------
|
| Define Demo module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

// Landing Page Navigation
Navigation::add('Pricing', '/#pricing', function (Section $section) {
    $section->attributes([
        'group' => 'landing',
        'slug' => 'pricing',
        'external' => true,
        'order' => 1,
    ]);
});

Navigation::add('Features', '/#features', function (Section $section) {
    $section->attributes([
        'group' => 'landing',
        'slug' => 'features',
        'external' => true,
        'order' => 0,
    ]);
});

// Navigation::add('FAQ', '/#faq', function (Section $section) {
//     $section->attributes([
//         'group' => 'landing',
//         'slug' => 'faq',
//         'external' => true,
//         'order' => 1,
//     ]);
// });

Navigation::add(
    'Docs',
    'https://saucebase-dev.github.io/docs/',
    function (Section $section) {
        $section->attributes([
            'group' => 'landing',
            'slug' => 'documentation',
            'external' => true,
            'newPage' => true,
            'order' => 1,
        ]);
    }
);

Navigation::add(
    'Github',
    'https://github.com/saucebase-dev/saucebase',
    function (Section $section) {
        $section->attributes([
            'group' => 'landing',
            'slug' => 'github',
            'external' => true,
            'newPage' => true,
            'order' => 2,
        ]);
    }
);
