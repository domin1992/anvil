<?php

namespace Anvil\Providers;

use Anvil\Commands\MakeCommand;

class CommandsProvider implements Provider
{
    /**
     * Commands shipped by the package. Project commands are auto-discovered
     * from the theme's `app/Commands` directory (any class exposing a static
     * `$name`).
     */
    protected array $commands = [
        MakeCommand::class,
    ];

    public function boot(): void
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }

        foreach (array_merge($this->commands, $this->discoverThemeCommands()) as $command) {
            $instance = new $command;
            \WP_CLI::add_command($instance::$name, $instance);
        }
    }

    /**
     * @return array<int, class-string>
     */
    protected function discoverThemeCommands(): array
    {
        $dir = get_template_directory().'/app/Commands';

        if (!is_dir($dir)) {
            return [];
        }

        $commands = [];

        foreach (glob($dir.'/*.php') ?: [] as $file) {
            $class = 'App\\Commands\\'.basename($file, '.php');

            if (class_exists($class) && isset($class::$name)) {
                $commands[] = $class;
            }
        }

        return $commands;
    }
}
