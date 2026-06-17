<?php

namespace Anvil\Filters;

use enshrined\svgSanitize\Sanitizer;

class SvgUploadPrefilter extends Filter
{
    public string|array $hook = 'wp_handle_upload_prefilter';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($upload)
    {
        if (!empty($upload['type']) && $upload['type'] === 'image/svg+xml') {
            $contents = file_get_contents($upload['tmp_name']);
            if (strpos($contents, '<?xml') === false) {
                file_put_contents($upload['tmp_name'], '<?xml version="1.0" encoding="UTF-8"?>'.$contents);
                $sanitizer = new Sanitizer;
                if (!$sanitizer->sanitize($contents)) {
                    $upload['error'] = __('Error', $contents);
                }
            }
        }

        return $upload;
    }
}
