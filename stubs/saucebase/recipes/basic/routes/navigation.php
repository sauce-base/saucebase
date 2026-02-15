<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| {Module} Module Navigation
|--------------------------------------------------------------------------
|
| Define {Module} module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

Navigation::add('{Module}', route('{module-}.index'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'badge' => [
            'content' => 'New',
            'variant' => 'info',
        ],
    ]);
});
