<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class IndexController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Index', [
            // Share here your frontend data, e.g. products, announcements, etc.
        ])->withSSR();
    }
}
