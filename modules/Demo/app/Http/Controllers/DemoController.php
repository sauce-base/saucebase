<?php

namespace Modules\Demo\Http\Controllers;

use Inertia\Inertia;
use Modules\Billing\Models\Product;

class DemoController
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke()
    {
        $data = [];

        if (app('modules')->isEnabled('Billing')) {
            $data['products'] = Product::displayable()->get();
        }

        return Inertia::render('Demo::Index', $data)->withSSR();
    }
}
