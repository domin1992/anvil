<?php

namespace Anvil\Providers;

use Anvil\Filters\Filter;
use Anvil\Support\Discovery;

class FiltersProvider implements Provider
{
    protected array $remove = [
        [
            'hook' => 'the_content',
            'function' => 'wpautop',
        ],
        [
            'hook' => 'the_excerpt',
            'function' => 'wpautop',
        ],
        [
            'hook' => 'render_block',
            'function' => 'wp_render_duotone_support',
        ],
        [
            'hook' => 'render_block',
            'function' => 'wp_restore_group_inner_container',
        ],
        [
            'hook' => 'render_block',
            'function' => 'wp_render_layout_support_flag',
        ],
        [
            'hook' => 'wp_mail',
            'function' => 'wp_staticize_emoji_for_email',
        ],
        [
            'hook' => 'the_content_feed',
            'function' => 'wp_staticize_emoji',
        ],
        [
            'hook' => 'comment_text_rss',
            'function' => 'wp_staticize_emoji',
        ],
    ];

    public function boot(): void
    {
        foreach (Discovery::classes('Filters', Filter::class) as $filter) {
            $instance = new $filter;
            if (is_array($instance->hook)) {
                foreach ($instance->hook as $hook) {
                    add_filter(
                        $hook,
                        [$instance, 'handle'],
                        $instance->priority,
                        $instance->accepted_args
                    );
                }
            } else {
                add_filter(
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
