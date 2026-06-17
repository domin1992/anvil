<?php

namespace Anvil\Filters;

class DisableXmlrpc extends Filter
{
    public string|array $hook = 'xmlrpc_enabled';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($is_enabled)
    {
        return false;
    }
}
