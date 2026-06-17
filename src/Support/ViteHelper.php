<?php

namespace Anvil\Support;

class ViteHelper
{
    private $hotFilePath = '';

    public function __construct()
    {
        $this->hotFilePath = get_template_directory().'/hot';
    }

    public function hotFileExists()
    {
        return file_exists($this->hotFilePath);
    }

    public function hotFileContent()
    {
        return $this->hotFileExists() ? file_get_contents($this->hotFilePath) : '';
    }

    public function viaManifest($src)
    {
        if ($this->hotFileExists()) {
            return sprintf('%s/%s/%s/%s', $this->hotFileContent(), 'wp-content/themes', env('WP_DEFAULT_THEME'), $src);
        }

        $manifest = json_decode(file_get_contents(get_template_directory().'/dist/.vite/manifest.json'));
        $src = $manifest->{$src}->file ?? null;

        if (!$src) {
            return;
        }

        return get_template_directory_uri().'/dist/'.$src;
    }
}
