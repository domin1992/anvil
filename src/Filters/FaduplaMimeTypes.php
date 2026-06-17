<?php

namespace Anvil\Filters;

class FaduplaMimeTypes extends Filter
{
    public string|array $hook = 'upload_mimes';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';

        return $mimes;
    }
}
