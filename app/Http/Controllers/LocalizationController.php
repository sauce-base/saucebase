<?php

namespace App\Http\Controllers;

use App\Settings\LocalizationSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    /**
     * Switch the session to another language.
     *
     * The trust boundary for the setting: the selector only offers enabled languages, but
     * nothing stops a client from posting any code, so the check happens here rather than
     * in the UI.
     */
    public function __invoke(string $locale): JsonResponse
    {
        $enabledLocales = array_keys(app(LocalizationSettings::class)->enabled());

        if (! in_array($locale, $enabledLocales, true)) {
            return new JsonResponse(['error' => 'Invalid locale'], 400);
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return new JsonResponse(['locale' => App::getLocale()]);
    }
}
