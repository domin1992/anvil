<?php

namespace Anvil\Filters;

class RemoveWpVersionStrings extends Filter
{
    public string|array $hook = [
        'script_loader_src',
        'style_loader_src',
    ];

    public int $priority = 10;

    public int $accepted_args = 2;

    public function handle($src, $handle)
    {
        global $wp_version;

        if (!($parsedUrl = parse_url($src, PHP_URL_QUERY))) {
            return $src;
        }

        parse_str($parsedUrl, $query);

        if (!empty($query['ver']) && $query['ver'] === $wp_version) {
            $src = remove_query_arg('ver', $src);
        }

        return $src;
    }
}
