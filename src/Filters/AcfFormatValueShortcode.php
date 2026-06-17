<?php

namespace Anvil\Filters;

class AcfFormatValueShortcode extends Filter
{
    public string|array $hook = [
        'acf/format_value/type=text',
        'acf/format_value/type=textarea',
    ];

    public int $priority = 10;

    public int $accepted_args = 3;

    public function handle($value, $post_id, $field)
    {
        return do_shortcode($value);
    }
}
