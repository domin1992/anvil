<?php

namespace Anvil\Providers;

use Anvil\Support\Discovery;
use Symfony\Component\Dotenv\Dotenv;

class AppProvider
{
    public static ?AppProvider $instance = null;

    protected array $providers = [
        \Anvil\Providers\FiltersProvider::class,
        \Anvil\Providers\ActionsProvider::class,
        \Anvil\Providers\RestApiProvider::class,
        \Anvil\Providers\JobsProvider::class,
        \Anvil\Providers\BlocksProvider::class,
        \Anvil\Providers\EnqueueAssetsProvider::class,
        \Anvil\Providers\CommandsProvider::class,
        \Anvil\Providers\NavMenusProvider::class,
        \Anvil\Providers\ImageSizesProvider::class,
        \Anvil\Providers\ThemeSupportProvider::class,
        \Anvil\Providers\OptionsPagesProvider::class,
        \Anvil\Providers\CustomPostTypesProvider::class,
    ];

    public static function init(): self
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->loadEnv();

        foreach ($this->bootableProviders() as $provider) {
            (new $provider)->boot();
        }
    }

    /**
     * The package's core providers followed by any project providers
     * auto-discovered from the theme's `app/Providers` directory.
     *
     * @return array<int, class-string>
     */
    protected function bootableProviders(): array
    {
        $themeProviders = Discovery::implementing('Providers', Provider::class, $this->providers);

        return array_merge($this->providers, $themeProviders);
    }

    public function loadEnv(): void
    {
        $dotenv = new Dotenv;
        $envFilePath = sprintf(
            '%s/.env',
            get_template_directory()
        );

        if (file_exists($envFilePath)) {
            $dotenv->load(
                $envFilePath
            );
        }
    }
}
