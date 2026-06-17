<?php

namespace Anvil\Jobs;

class ClearBackupsJob extends Job
{
    protected function setInterval()
    {
        $this->hourly();
    }

    public function handle()
    {
        $basePath = wp_get_upload_dir()['basedir'] . '/zncr/';

        $files = array_values(
            array_filter(
                scandir($basePath),
                fn (string $file) => !str_starts_with($file, '.')
            )
        );

        foreach ($files as $file) {
            $filePath = $basePath . $file;

            if (file_exists($filePath) && (time() - filemtime($filePath)) > 3600) {
                unlink($filePath);
            }
        }
    }
}
