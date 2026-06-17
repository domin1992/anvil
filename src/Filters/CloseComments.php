<?php

namespace Anvil\Filters;

class CloseComments extends Filter
{
    public array|string $hook = 'comments_open';

    public int $priority = 10;

    public int $accepted_args = 2;

    public function handle($open, $post_id)
    {
        return false;
    }
}
