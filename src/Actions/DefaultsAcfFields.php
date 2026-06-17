<?php

namespace Anvil\Actions;

class DefaultsAcfFields extends Action
{
    public string $hook = 'acf/init';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(
                [
                    'key' => 'group_5f1e9925dfb57',
                    'title' => 'Custom post type',
                    'fields' => [
                        [
                            'key' => 'field_5f1e9974a96ae',
                            'label' => 'Select post type',
                            'name' => 'post_type',
                            'type' => 'post_type_selector',
                            'instructions' => 'Select post type to set default template',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => [
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ],
                            'select_type' => 0,
                        ],
                    ],
                    'location' => [
                        [
                            [
                                'param' => 'post_type',
                                'operator' => '==',
                                'value' => 'defaults',
                            ],
                        ],
                    ],
                    'menu_order' => 1,
                    'position' => 'side',
                    'style' => 'default',
                    'label_placement' => 'top',
                    'instruction_placement' => 'label',
                    'hide_on_screen' => '',
                    'active' => true,
                    'description' => '',
                ]
            );
        }
    }
}
