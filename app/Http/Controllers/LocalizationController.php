<?php

namespace App\Http\Controllers;

use App\Settings\LocalizationSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    /**
     * Switch the session to another language.
     *
     * The trust boundary for the setting: the selector only offers enabled languages, but
     * nothing stops a client from posting any code, so the check happens here.
     *
     * Assigned rather than mass-assigned, which keeps `locale` off `$fillable` until
     * something actually needs to fill it from input.
     */
    public function __invoke(Request $request, string $locale): JsonResponse
    {
        $enabledLocales = array_keys(app(LocalizationSettings::class)->enabled());

        if (! in_array($locale, $enabledLocales, true)) {
            return new JsonResponse(['error' => 'Invalid locale'], 400);
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        if ($user = $request->user()) {
            $user->locale = $locale;
            $user->save();
        }

        return new JsonResponse(['locale' => App::getLocale()]);
    }
}
