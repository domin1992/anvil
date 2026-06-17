<?php

namespace Anvil\Filters;

class ClosePings extends Filter
{
    public string|array $hook = 'pings_open';

    public int $priority = 20;

    public int $accepted_args = 2;

    public function handle($open, $post_id)
    {
        return false;
    }
}
