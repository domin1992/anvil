<?php

namespace Anvil\Actions;

abstract class Action
{
    public string $hook;

    public int $priority = 10;

    public int $accepted_args = 1;
}
