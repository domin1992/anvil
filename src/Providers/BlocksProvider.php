<?php

namespace Anvil\Providers;

class BlocksProvider implements Provider
{
    public function boot(): void
    {
        add_action('init', [$this, 'loadFromJson']);
    }

    public function loadFromJson(): void
    {
        $basePaths = [
            dirname(__DIR__, 2).'/blocks',   // package default blocks
            get_template_directory().'/blocks', // theme blocks
        ];

        foreach ($basePaths as $basePath) {
            if (!is_dir($basePath)) {
                continue;
            }

            foreach (scandir($basePath) as $dir) {
                if (!in_array($dir, ['..', '.'])) {
                    $blockJsonPath = $basePath.'/'.$dir.'/block.json';
                    if (file_exists($blockJsonPath)) {
                        register_block_type($blockJsonPath);
                    }
                }
            }
        }
    }
}
