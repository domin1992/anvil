<?php

namespace Anvil\Providers;

use Anvil\Support\Discovery;
use Anvil\Support\ViteHelper;
use App\Enqueue\Enqueue;

class EnqueueAssetsProvider implements Provider
{
    protected array $dequeueStyles = [
        'wp-block-library',
        'wp-block-library-theme',
        'wc-block-style',
        'classic-theme-styles',
    ];

    protected array $dequeueScripts = [
        'jquery',
    ];

    public function boot(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'viteDevMode']);
        add_action('wp_enqueue_scripts', [$this, 'registerBlocksAssets']);
        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueThemeStyle']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets']);
        add_action('wp_enqueue_scripts', [$this, 'dequeueAssets']);
    }

    public function viteDevMode(): void
    {
        $viteHelper = new ViteHelper;

        if ($viteHelper->hotFileExists()) {
            wp_enqueue_script('vite-client', $viteHelper->hotFileContent().'/wp-content/themes/zncr/@vite/client', [], null, false);
        }
    }

    public function registerBlocksAssets(): void
    {
        $viteHelper = new ViteHelper;

        $basePath = get_template_directory().'/blocks';

        if (!is_dir($basePath)) {
            return;
        }

        foreach (scandir($basePath) as $dir) {
            if (!in_array($dir, ['..', '.'])) {
                $blockJsonPath = $basePath.'/'.$dir.'/block.json';
                $blockJson = json_decode(file_get_contents($blockJsonPath));

                $blockName = explode('/', $blockJson->name);

                if (isset($blockJson->script) || isset($blockJson->viewScript)) {
                    wp_register_script(
                        $blockJson->script ?? $blockJson->viewScript,
                        $viteHelper->viaManifest(
                            sprintf(
                                'blocks/%s/%s.js',
                                $dir,
                                $blockName[1]
                            )
                        ),
                        [],
                        false,
                        true
                    );
                }

                if (isset($blockJson->style)) {
                    wp_register_style(
                        $blockJson->style,
                        $viteHelper->viaManifest(
                            sprintf(
                                'blocks/%s/%s.css',
                                $dir,
                                $blockName[1]
                            )
                        ),
                        [],
                        false,
                        'all'
                    );
                }
            }
        }
    }

    /**
     * Discovered Enqueue classes (package + theme) for a given context.
     *
     * @return array<int, class-string>
     */
    protected function enqueuesFor(string $context): array
    {
        // The Enqueue base class lives in the project theme; nothing to do if
        // the project does not define any enqueues.
        if (!class_exists(Enqueue::class)) {
            return [];
        }

        return array_values(array_filter(
            Discovery::classes('Enqueue', Enqueue::class),
            fn (string $class) => (new $class)->context === $context
        ));
    }

    public function wpEnqueueScripts(): void
    {
        foreach ($this->enqueuesFor('front') as $enqueue) {
            (new $enqueue)->handle();
        }
    }

    public function adminEnqueueScripts(): void
    {
        foreach ($this->enqueuesFor('admin') as $enqueue) {
            (new $enqueue)->handle();
        }
    }

    public function enqueueBlockEditorAssets(): void
    {
        foreach ($this->enqueuesFor('editor') as $enqueue) {
            (new $enqueue)->handle();
        }
    }

    public function enqueueThemeStyle(): void
    {
        wp_enqueue_style('style', get_stylesheet_uri());
    }

    public function dequeueAssets(): void
    {
        foreach ($this->dequeueStyles as $asset) {
            wp_dequeue_style($asset);
            wp_deregister_style($asset);
        }

        if (!is_admin() && !is_login_page()) {
            foreach ($this->dequeueScripts as $asset) {
                wp_deregister_script($asset);
            }
        }
    }
}
