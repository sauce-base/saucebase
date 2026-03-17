<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Roadmap Module Navigation
|--------------------------------------------------------------------------
|
| Define Roadmap module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

Navigation::add('Roadmap', route('roadmap.index'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'roadmap',
        'badge' => [
            'content' => 'New',
            'variant' => 'info',
        ],
    ]);
});
