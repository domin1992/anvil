<?php

namespace Anvil\Actions;

class FixMediaViewsCss extends Action
{
    public string $hook = 'admin_footer';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        echo '<link rel="stylesheet" id="fix-media-views-css" href="'.get_bloginfo('url').'/wp-includes/css/media-views.min.css?ver=5.9.1" media="all">';
    }
}
