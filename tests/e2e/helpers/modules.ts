import type { Laravel } from '@saucebase/laravel-playwright';

/**
 * Checks whether a module is installed in this checkout, mirroring the
 * frontend's `modules().has(name)` convention for tests that need real
 * module-owned UI (not just proof of authentication) and must skip when that
 * UI belongs to a different combination of installed modules.
 */
export async function isModuleInstalled(
    laravel: Laravel,
    name: string,
): Promise<boolean> {
    return laravel.callFunction<boolean>('Tests\\Support\\ModuleSupport::has', [
        name,
    ]);
}
