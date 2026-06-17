<?php

namespace Anvil\Actions;

class LoginLogo extends Action
{
    public string $hook = 'login_enqueue_scripts';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        $logo = get_field('header_logo', 'options');
        if (!$logo) {
            return;
        }
        $logo_url = esc_url($logo['url']);
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: url(<?= $logo_url; ?>);
                height: 4.0625rem;
                width: 20rem;
                max-width: 20rem;
                background-size: 13rem 4.0625rem;
                background-repeat: no-repeat;
                padding-bottom: 1.875rem;
            }
        </style>
        <?php
    }
}
