<?php

namespace Anvil\Filters;

class DisableTinymceEmoji extends Filter
{
    public string|array $hook = 'tiny_mce_plugins';

    public int $priority = 10;

    public int $accepted_args = 2;

    public function handle($plugins, $editor_id)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, ['wpemoji']);
        }

        return [];
    }
}
