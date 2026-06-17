<?php

namespace Anvil\Filters;

class FilterRemoveLazyLoadingOnDemand extends Filter
{
    public string|array $hook = 'wp_get_attachment_image_attributes';

    public int $accepted_args = 3;

    public function handle($attr, $attachment, $size)
    {
        if (isset($attr['no-lazy']) && $attr['no-lazy']) {
            unset($attr['loading']);
            unset($attr['no-lazy']);
        }

        return $attr;
    }
}
