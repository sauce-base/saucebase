import { test as setup } from '@saucebase/laravel-playwright';

setup('setup the database', async ({ laravel }) => {
    await laravel.artisan('migrate:fresh');
    await laravel.artisan('db:seed');
    await laravel.artisan('modules:seed');
});
