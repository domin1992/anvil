<?php

namespace Anvil\Providers;

use Anvil\Actions\Action;
use Anvil\Support\Discovery;

class ActionsProvider implements Provider
{
    protected array $remove = [
        [
            'hook' => 'wp_head',
            'function' => 'wp_generator',
        ],
        [
            'hook' => 'wp_enqueue_scripts',
            'function' => 'wp_enqueue_global_styles',
        ],
        [
            'hook' => 'wp_footer',
            'function' => 'wp_enqueue_global_styles',
            'priority' => 1,
        ],
        [
            'hook' => 'wp_body_open',
            'function' => 'wp_global_styles_render_svg_filters',
        ],
        [
            'hook' => 'admin_print_styles',
            'function' => 'print_emoji_styles',
        ],
        [
            'hook' => 'wp_head',
            'function' => 'print_emoji_detection_script',
            'priority' => 7,
        ],
        [
            'hook' => 'admin_print_scripts',
            'function' => 'print_emoji_detection_script',
        ],
        [
            'hook' => 'wp_print_styles',
            'function' => 'print_emoji_styles',
        ],
    ];

    public function boot(): void
    {
        foreach (Discovery::classes('Actions', Action::class) as $action) {
            $instance = new $action;
            if (is_array($instance->hook)) {
                foreach ($instance->hook as $hook) {
                    add_action(
                        $hook,
                        [$instance, 'handle'],
                        $instance->priority,
                        $instance->accepted_args
                    );
                }
            } else {
                add_action(
                    $instance->hook,
                    [$instance, 'handle'],
                    $instance->priority,
                    $instance->accepted_args
                );
            }
        }

        foreach ($this->remove as $remove) {
            remove_action(
                $remove['hook'],
                $remove['function'],
                $remove['priority'] ?? 10
            );
        }
    }
}
