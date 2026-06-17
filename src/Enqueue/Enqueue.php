<?php

namespace Anvil\Enqueue;

use Anvil\Support\ViteHelper;

abstract class Enqueue
{
    /**
     * Where this enqueue runs: 'front' (wp_enqueue_scripts),
     * 'admin' (admin_enqueue_scripts) or 'editor' (enqueue_block_editor_assets).
     */
    public string $context = 'front';

    public function handle(): void {}

    /**
     * Enqueues Assets
     *
     * Example usage:
     * $this->enqueueAsset( 'style', 'default-page-styles', 'dist/default_page.css', array(), 'all' );
     * $this->enqueueAsset( 'script', 'default-page-scripts', 'dist/default_page.bundle.js', array(), true );
     *
     * @deprecated use enqueueViteAsset instead
     */
    protected function enqueueAsset(
        string $type,
        string $handle,
        string $file_path,
        array $deps = [],
        mixed ...$args
    ): void {
        if (!file_exists(get_template_directory().'/'.$file_path)) {
            return;
        }

        $in_footer = apply_filters('zncr_load_js_in_footer', true);

        // Get last param of args.
        $last_parameter = isset($args[0]) ? $args[0] : null;

        // Generate enqueue function based on type.
        $func = sprintf('wp_enqueue_%s', $type);
        if ($last_parameter !== null || $type === 'script') {
            // When last param or type is script.
            $func(
                $handle,
                sprintf('%s/%s', get_template_directory(), $file_path),
                $deps,
                false,
                // If last param available apply,
                // otherwise zncr setting to load scripts in footer.
                $last_parameter !== null ? $last_parameter : $in_footer
            );
        } else {
            $func(
                $handle,
                sprintf('%s/%s', get_template_directory(), $file_path),
                $deps,
                false
            );
        }
    }

    public function viteAsset($src)
    {
        $manifest = json_decode(file_get_contents(get_template_directory().'/dist/manifest.json'));
        $src = $manifest->{$src}->file ?? null;

        if (!$src) {
            return null;
        }

        return get_template_directory_uri().'/dist/'.$src;
    }

    protected function enqueueViteAsset($type, $handle, $originalSrc)
    {
        if (!file_exists(sprintf('%s/%s', get_template_directory(), $originalSrc))) {
            return;
        }

        $func = sprintf('wp_enqueue_%s', $type);

        $viteHelper = new ViteHelper;

        if ($viteHelper->hotFileExists()) {
            $func(
                $handle,
                sprintf('%s/%s/%s/%s', $viteHelper->hotFileContent(), 'wp-content/themes', env('WP_DEFAULT_THEME'), $originalSrc),
                [],
                false,
                $type === 'script' ? true : 'all'
            );
        }

        $src = $this->viteManifest()->{$originalSrc}->file ?? null;

        if (!$src) {
            return;
        }

        $func(
            $handle,
            get_template_directory_uri().'/dist/'.$src,
            [],
            false,
            $type === 'script' ? true : 'all'
        );
    }

    private function viteManifest()
    {
        return json_decode(file_get_contents(get_template_directory().'/dist/.vite/manifest.json'));
    }
}
