<?php

use App\Facades\Navigation;
use App\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Blog Module Navigation
|--------------------------------------------------------------------------
|
| Define Blog module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

Navigation::add('Blog', route('blog.index'), function (Section $section) {
    $section->attributes([
        'group' => 'landing',
        'slug' => 'blog',
        'icon' => 'blog',
        'order' => 1,
    ]);
});
