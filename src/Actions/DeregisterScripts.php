<?php

namespace Anvil\Actions;

class DeregisterScripts extends Action
{
    public string $hook = 'wp_footer';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        wp_deregister_script('wp-embed');
    }
}
