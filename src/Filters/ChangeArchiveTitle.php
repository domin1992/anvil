<?php

namespace Anvil\Filters;

class ChangeArchiveTitle extends Filter
{
    public string|array $hook = 'get_the_archive_title';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($title, $sep, $seplocation)
    {
        $title = str_replace('Archiwum ', '', $title);

        return $title;
    }
}
