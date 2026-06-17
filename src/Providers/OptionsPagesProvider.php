<?php

namespace Anvil\Providers;

class OptionsPagesProvider implements Provider
{
    public function boot(): void
    {
        add_action('init', [$this, 'addOptiomPages']);
    }

    public function addOptiomPages() {
        if (!function_exists('acf_add_options_page') || !function_exists('acf_add_options_sub_page')) {
            return;
        }

        $options_pages = config('options-pages.options-pages');
        if (is_array($options_pages)) {
            foreach ($options_pages as $options_page) {
                acf_add_options_page(
                    $options_page
                );

                if (
                    isset($options_page['sub_pages'])
                    && is_array($options_page['sub_pages'])
                ) {
                    foreach ($options_page['sub_pages'] as $options_sub_page) {
                        $options_sub_page['parent_slug'] = $options_page['menu_slug'];
                        acf_add_options_sub_page($options_sub_page);
                    }
                }
            }
        }
    }
}
