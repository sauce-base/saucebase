<?php

use App\Facades\Navigation;
use App\Navigation\Section;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Billing Module Navigation
|--------------------------------------------------------------------------
|
| Define Billing module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

// User menu - Upgrade
Navigation::addWhen(fn () => ! Auth::user()?->isSubscriber(), 'Upgrade', '/#pricing', function (Section $section) {
    $section->attributes([
        'group' => 'user',
        'slug' => 'upgrade',
        'icon' => 'upgrade',
        'order' => 0,
        'class' => 'text-yellow-600 hover:text-yellow-700 dark:hover:text-yellow-400',
    ]);
});

// Settings sidebar - Billing
Navigation::add('Billing', route('settings.billing'), function (Section $section) {
    $section->attributes([
        'group' => 'settings',
        'slug' => 'billing',
        'icon' => 'billing',
        'order' => 30,
    ]);
});
