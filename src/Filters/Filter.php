<?php

namespace Anvil\Filters;

abstract class Filter
{
    public string|array $hook;

    public int $priority = 10;

    public int $accepted_args = 1;
}
