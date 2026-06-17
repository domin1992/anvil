<?php

namespace Anvil\Filters;

class AcfPopulateNavMenuSelect extends Filter
{
    public string|array $hook = ['acf/load_field/name=nav_menu'];

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($field)
    {
        $menus = wp_get_nav_menus();

        if ($menus) {
            $choices = [];
            foreach ($menus as $menu) {
                $choices[$menu->slug] = $menu->name;
            }
            $field['choices'] = $choices;
        }

        return $field;
    }
}
