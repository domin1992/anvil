<?php

namespace Anvil\Providers;

class ThemeSupportProvider implements Provider
{
    public function boot(): void
    {
        $themeSupport = config('theme-support');
        if (is_array($themeSupport)) {
            foreach ($themeSupport as $key => $themeSupportItem) {
                if (is_string($key)) {
                    add_theme_support($key, $themeSupportItem);
                } else {
                    add_theme_support($themeSupportItem);
                }
            }
        }
    }
}
