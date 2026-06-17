<?php

namespace Anvil\Actions;

class RemoveToolbarNodes extends Action
{
    public string $hook = 'admin_bar_menu';

    public int $priority = 1000;

    public int $accepted_args = 1;

    public function handle(...$args)
    {
        [$wp_admin_bar] = $args;
        $wp_admin_bar->remove_node('comments');
        $wp_admin_bar->remove_node('customize');
        $wp_admin_bar->remove_node('updates');
        $wp_admin_bar->remove_node('wpseo-menu');

        return $wp_admin_bar;
    }
}
