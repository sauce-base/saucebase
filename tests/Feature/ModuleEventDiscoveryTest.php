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
            base_path('modules/NonExistentModule/app/Listeners/SomeListener.php')
        );

        $className = call_user_func($callback, $file, base_path());

        $this->assertEquals(
            'Modules\NonExistentModule\Listeners\SomeListener',
            $className,
            'Module listener class name must not include the "app" folder in the namespace.'
        );
    }

    public function test_unregistered_module_listener_class_name_is_not_null(): void
    {
        $callback = DiscoverEvents::$guessClassNamesUsingCallback;
        $this->assertNotNull($callback);

        // A file from a module not in the registry → Module::find() returns null
        // → callback computes class name normally (only explicitly disabled modules return null)
        $file = new \SplFileInfo(
            base_path('modules/NonExistentModule/app/Listeners/SomeListener.php')
        );

        $className = call_user_func($callback, $file, base_path());

        $this->assertNotNull($className, 'Listeners from unregistered modules must not return null.');
    }
}
