<?php

namespace Anvil\Support;

class ConfigLoader
{
    public function load($path): mixed
    {
        // Return null when path no provided
        if (!$path) {
            return null;
        }

        // Split config by dots
        $path_exploded = explode('.', $path);

        // First element is config file name
        $file_name = $path_exploded[0];

        // Config lives in the project theme (app/Config).
        $config_file = sprintf('%s/app/Config/%s.php', get_template_directory(), $file_name);

        // Return null when the config file does not exist
        if (!file_exists($config_file)) {
            return null;
        }

        // Read config file
        $config = include $config_file;

        // Look for specific option in loaded config file
        if (count($path_exploded) > 1) {
            for ($i = 1; $i < count($path_exploded); $i++) {
                if (!isset($config[$path_exploded[$i]])) {
                    return null;
                }

                $config = $config[$path_exploded[$i]];
            }
        }

        return $config;
    }
}
