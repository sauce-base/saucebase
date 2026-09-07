<?php

namespace Tests\Support;

use App\Models\User;
use InterNACHI\Modular\Support\ModuleRegistry;

class ModuleSupport
{
    public static function has(string $name): bool
    {
        return app(ModuleRegistry::class)->module($name) !== null;
    }

    /**
     * Let every installed module put an E2E user into a state where its own UI works.
     *
     * Without this, a module's specs have to know which *other* modules are installed:
     * tenancy funnels a workspace-less visitor into onboarding, so auth's dashboard
     * chrome specs had to skip themselves whenever tenancy was present. A module is
     * installable, so the combination it lands in is not something its tests can
     * enumerate — the module that imposes the requirement is the one that can satisfy it.
     *
     * Opt in by convention rather than an interface: define
     * `Modules\<Name>\Tests\Support\E2eUser::prepare(User $user)` and it gets called.
     * Modules with nothing to do define nothing.
     */
    public static function prepareUser(User $user): void
    {
        foreach (app(ModuleRegistry::class)->modules() as $module) {
            $preparer = trim($module->namespace(), '\\').'\\Tests\\Support\\E2eUser';

            if (class_exists($preparer)) {
                $preparer::prepare($user);
            }
        }
    }
}
