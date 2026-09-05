import { test as setup } from '@saucebase/laravel-playwright';

setup('setup the database', async ({ laravel }) => {
    await laravel.artisan('migrate:fresh');
    await laravel.artisan('db:seed');
    await laravel.artisan('modules:seed');

    // Shared fixed-email accounts are created here, once, rather than per test.
    // This project runs on its own before any worker starts, so the writes cannot
    // race. See Tests\Support\TestFixtures::seedSharedAccounts().
    await laravel.callFunction(
        'Tests\\Support\\TestFixtures::seedSharedAccounts',
    );
});
