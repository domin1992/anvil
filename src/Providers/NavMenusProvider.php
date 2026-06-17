<?php

namespace Anvil\Providers;

class NavMenusProvider implements Provider
{
    public function boot(): void
    {
        if (function_exists('register_nav_menus')) {
            register_nav_menus(
                config('nav-menus.nav-menus')
            );
        }
    }
}
