<?php

namespace Anvil\Filters;

class FilterScriptLoaderTag extends Filter
{
    public string|array $hook = 'script_loader_tag';

    public int $accepted_args = 3;

    public function handle($tag, $handle, $src)
    {
        $themePath = '/wp-content/themes/'.get_template().'/';

        if (is_admin() || $handle == 'recaptcha' || strpos($src, $themePath) === false) {
            return $tag;
        }

        return str_replace(' src', ' type="module" src', $tag);
    }
}
