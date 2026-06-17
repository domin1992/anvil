<?php

namespace Anvil\Filters;

class YoastBottom extends Filter
{
    public string|array $hook = 'wpseo_metabox_prio';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        return 'low';
    }
}
