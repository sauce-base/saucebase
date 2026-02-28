<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Settings Module Navigation
|--------------------------------------------------------------------------
|
| Define Settings module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

// User menu - Settings
Navigation::add('Settings', route('settings.index'), function (Section $section) {
    $section->attributes([
        'group' => 'user',
        'slug' => 'settings',
        'order' => 10,
    ]);
});

// Settings sidebar - General
Navigation::add('General', route('settings.index'), function (Section $section) {
    $section->attributes([
        'group' => 'settings',
        'slug' => 'settings',
        'order' => 10,
    ]);
});

// Settings sidebar - Profile
Navigation::add('Profile', route('settings.profile'), function (Section $section) {
    $section->attributes([
        'group' => 'settings',
        'slug' => 'profile',
        'order' => 20,
    ]);
});

// Secondary navigation - Settings
Navigation::add('Settings', route('settings.index'), function (Section $section) {
    $section->attributes([
        'group' => 'secondary',
        'slug' => 'settings',
        'order' => 10,
        'badge' => [
            'content' => '1',
            'variant' => 'destructive',
        ],
    ]);
});
