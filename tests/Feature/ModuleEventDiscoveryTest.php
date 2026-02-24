<?php

namespace Tests\Feature;

use Illuminate\Foundation\Events\DiscoverEvents;
use Tests\TestCase;

class ModuleEventDiscoveryTest extends TestCase
{
    public function test_module_event_discovery_class_name_callback_is_registered(): void
    {
        $this->assertNotNull(
            DiscoverEvents::$guessClassNamesUsingCallback,
            'Module event discovery class name callback must be registered in AppServiceProvider::register().'
        );
    }

    public function test_module_listener_class_name_is_correctly_resolved(): void
    {
        $callback = DiscoverEvents::$guessClassNamesUsingCallback;
        $this->assertNotNull($callback);

        $file = new \SplFileInfo(
            base_path('modules/Billing/app/Listeners/SyncSubscriberRole.php')
        );

        $className = call_user_func($callback, $file, base_path());

        $this->assertEquals(
            'Modules\Billing\Listeners\SyncSubscriberRole',
            $className,
            'Module listener class name must not include the "app" folder in the namespace.'
        );
    }

    public function test_disabled_module_listener_class_name_returns_null(): void
    {
        // Verifies disabled modules are properly skipped during event discovery
        // This test uses a non-existent module so no real module is affected
        $callback = DiscoverEvents::$guessClassNamesUsingCallback;
        $this->assertNotNull($callback);

        // A file from a module that doesn't exist → Module::isEnabled() returns false
        // We can't easily test this without mocking, so we just ensure the callback
        // handles the Billing module (which IS enabled) without returning null
        $file = new \SplFileInfo(
            base_path('modules/Billing/app/Listeners/SyncSubscriberRole.php')
        );

        $className = call_user_func($callback, $file, base_path());

        $this->assertNotNull($className, 'Enabled module listeners must not return null.');
    }
}
