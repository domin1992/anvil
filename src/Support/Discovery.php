<?php

namespace Anvil\Support;

use ReflectionClass;

/**
 * Discovers classes living in a given sub-namespace of either the Anvil package
 * (the framework defaults) or the project theme (`app/` extensions).
 *
 * This is what lets a project drop a class into e.g. `app/Actions` and have it
 * registered automatically on top of the package's own `Actions`.
 */
class Discovery
{
    /**
     * Absolute path to the Anvil package `src` directory.
     */
    public static function coreSrc(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Absolute path to the theme `app` directory.
     */
    public static function themeApp(): string
    {
        return get_template_directory().'/app';
    }

    /**
     * Roots to scan for a given sub-directory: the package default first, then
     * the theme override/extension second. Each root is [path, namespace].
     *
     * @return array<int, array{path: string, ns: string}>
     */
    public static function roots(string $subdir): array
    {
        return [
            ['path' => self::coreSrc().'/'.$subdir, 'ns' => 'Anvil\\'.$subdir.'\\'],
            ['path' => self::themeApp().'/'.$subdir, 'ns' => 'App\\'.$subdir.'\\'],
        ];
    }

    /**
     * Discover concrete classes in `$subdir` (across package + theme) that are
     * sub-classes of `$baseClass`. The abstract base itself is skipped.
     *
     * @return array<int, class-string>
     */
    public static function classes(string $subdir, string $baseClass): array
    {
        $found = [];

        foreach (self::roots($subdir) as $root) {
            if (!is_dir($root['path'])) {
                continue;
            }

            foreach (glob($root['path'].'/*.php') ?: [] as $file) {
                $class = $root['ns'].basename($file, '.php');

                if (!class_exists($class)) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || !$reflection->isSubclassOf($baseClass)) {
                    continue;
                }

                $found[] = $class;
            }
        }

        return $found;
    }

    /**
     * Discover concrete classes in `$subdir` implementing `$interface`,
     * optionally excluding a set of already-known classes.
     *
     * @param  array<int, class-string>  $exclude
     * @return array<int, class-string>
     */
    public static function implementing(string $subdir, string $interface, array $exclude = []): array
    {
        $found = [];

        foreach (self::roots($subdir) as $root) {
            if (!is_dir($root['path'])) {
                continue;
            }

            foreach (glob($root['path'].'/*.php') ?: [] as $file) {
                $class = $root['ns'].basename($file, '.php');

                if (!class_exists($class) || in_array($class, $exclude, true)) {
                    continue;
                }

                $reflection = new ReflectionClass($class);

                if ($reflection->isAbstract() || !$reflection->implementsInterface($interface)) {
                    continue;
                }

                $found[] = $class;
            }
        }

        return $found;
    }

    /**
     * PHP files matching `*.php` inside `$subdir` across package + theme roots.
     * Useful for non-class config-style files (e.g. RestApiRoutes).
     *
     * @return array<int, string>
     */
    public static function files(string $subdir): array
    {
        $files = [];

        foreach (self::roots($subdir) as $root) {
            if (!is_dir($root['path'])) {
                continue;
            }

            foreach (glob($root['path'].'/*.php') ?: [] as $file) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
