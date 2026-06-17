<?php

namespace Anvil\Actions;

class SecurityHeaders extends Action
{
    public string $hook = 'send_headers';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        header('X-FRAME-OPTIONS: SAMEORIGIN');
    }
}
