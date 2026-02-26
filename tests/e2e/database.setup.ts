import { test as setup } from '@saucebase/laravel-playwright';

setup('setup the database', async ({ laravel }) => {
    await laravel.artisan('migrate:fresh --seed');
    await laravel.artisan('module:migrate-refresh --all --seed');
});
