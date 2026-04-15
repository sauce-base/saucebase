<?php

namespace Modules\Billing\Http\Controllers;

use Inertia\Response;
use Modules\Billing\Models\Product;

class BillingPlansController
{
    public function __invoke(): Response
    {
        return inertia('Billing::Plans', [
            'products' => Product::displayable()->get(),
        ])->withSSR();
    }
}
