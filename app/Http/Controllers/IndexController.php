<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Modules\Billing\Models\Product;

class IndexController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Index', [
            'products' => Product::displayable()->get(),
        ])->withSSR();
    }
}
