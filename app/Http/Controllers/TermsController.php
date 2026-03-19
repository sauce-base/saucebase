<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class TermsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Terms')->withSSR();
    }
}
