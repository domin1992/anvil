<?php

namespace Anvil\Support;

trait HasAccessToFilesystem
{
    private $wpFilesystem;

    private function initFilesystem()
    {
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH.'wp-admin/includes/file.php';
        }

        \WP_Filesystem();

        global $wp_filesystem;

        $this->wpFilesystem = $wp_filesystem;
    }
}
