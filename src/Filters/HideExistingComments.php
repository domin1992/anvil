<?php

namespace Anvil\Filters;

class HideExistingComments extends Filter
{
    public string|array $hook = 'comments_array';

    public int $priority = 10;

    public int $accepted_args = 2;

    public function handle($comments, $post_id)
    {
        __return_empty_array();
    }
}
