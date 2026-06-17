<?php

namespace Anvil\Actions;

class PrefixOutputBufferStart extends Action
{
    public string $hook = 'wp_loaded';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        ob_start(function ($buffer) {
            return preg_replace("%[ ]type=['\"]text/(javascript|css)['\"]%", '', $buffer);
        });
    }
}
