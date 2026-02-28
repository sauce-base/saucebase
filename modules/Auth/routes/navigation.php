<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Auth Module Navigation
|--------------------------------------------------------------------------
|
| Define Auth module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

// User menu - Logout
Navigation::add('Log out', '#', function (Section $section) {
    $section->attributes([
        'group' => 'user',
        'action' => 'logout',
        'slug' => 'logout',
        'order' => 100,
    ]);
});
