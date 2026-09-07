<?php

namespace App\Settings;

use App\Providers\SettingsServiceProvider;
use Illuminate\Support\Collection;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

/**
 * Finds the settings sections this installation has.
 *
 * Discovery rather than registration, mirroring how module settings classes are
 * already found (see {@see SettingsServiceProvider}): a module
 * contributes a section by having the class, not by remembering a registration
 * line, and an uninstalled module contributes nothing without cleaning up after
 * itself.
 *
 * Both kinds of class share the `src/Settings` directory. Spatie keeps what is a
 * `Settings` subclass and this keeps what is a {@see SettingsSection}, so neither
 * discovery sees the other's classes.
 */
class SectionRegistry
{
    /** @var array<int, class-string<SettingsSection>>|null */
    private ?array $discovered = null;

    public function __construct(private readonly ModuleRegistry $modules) {}

    /**
     * Every section that applies to this request, in sidebar order.
     *
     * @return Collection<int, SettingsSection>
     */
    public function all(): Collection
    {
        return collect($this->discover())
            ->map(fn (string $class): SettingsSection => app($class))
            ->filter(fn (SettingsSection $section): bool => $section->visible())
            ->sortBy(fn (SettingsSection $section): int => $section->order())
            ->values();
    }

    /**
     * The sidebar's view of the sections: what the front-end needs to list them.
     *
     * @return array<int, array{slug: string, title: string, icon: string|null, component: string}>
     */
    public function forFrontend(): array
    {
        return $this->all()
            ->map(fn (SettingsSection $section): array => [
                'slug' => $section->slug(),
                'title' => $section->title(),
                'icon' => $section->icon(),
                'component' => $section->component(),
            ])
            ->all();
    }

    /**
     * Scan core and every module for section classes.
     *
     * Cached per instance rather than per request: the filesystem walk is the
     * expensive part and the answer cannot change within a request.
     *
     * @return array<int, class-string<SettingsSection>>
     */
    private function discover(): array
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $directories = collect([app_path('Settings')])
            ->merge($this->modules->modules()->map(
                fn (ModuleConfig $module): string => $module->path('src/Settings'),
            ))
            ->filter(fn (string $path): bool => is_dir($path))
            ->all();

        if ($directories === []) {
            return $this->discovered = [];
        }

        return $this->discovered = collect((new Finder)->files()->name('*.php')->in($directories))
            ->map(fn (SplFileInfo $file): string => $this->classNameFor($file))
            ->filter(function (string $class): bool {
                try {
                    return is_subclass_of($class, SettingsSection::class)
                        && (new ReflectionClass($class))->isInstantiable();
                } catch (Throwable) {
                    return false;
                }
            })
            ->values()
            ->all();
    }

    /**
     * PSR-4 means the path under the autoloader's root maps onto the namespace,
     * so the class name can be read off the file path without loading the file.
     */
    private function classNameFor(SplFileInfo $file): string
    {
        $path = str_replace(['/', '\\'], '\\', $file->getRelativePathname());
        $class = substr($path, 0, -strlen('.php'));

        foreach ($this->namespaceRoots() as $directory => $namespace) {
            if (str_starts_with($file->getPath(), $directory)) {
                return $namespace.'\\'.$class;
            }
        }

        return $class;
    }

    /**
     * @return array<string, string>
     */
    private function namespaceRoots(): array
    {
        $roots = [app_path('Settings') => 'App\\Settings'];

        foreach ($this->modules->modules() as $module) {
            $roots[$module->path('src/Settings')] = $module->namespace().'Settings';
        }

        return $roots;
    }
}
