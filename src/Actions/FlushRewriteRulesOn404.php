<?php

namespace Anvil\Actions;

class FlushRewriteRulesOn404 extends Action
{
    public string $hook = 'init';

    public function handle()
    {
        if (is_404()) {
            flush_rewrite_rules();
        }
    }
}
