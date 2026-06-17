<?php

namespace Anvil\Filters;

class DefaultTemplateContent extends Filter
{
    public string|array $hook = 'default_content';

    public int $priority = 10;

    public int $accepted_args = 2;

    public function handle($content, $post)
    {
        if (!function_exists('get_field')) {
            return '';
        }
        $the_query = new \WP_Query([
            'post_type" => "defaults',
        ]);
        if ($the_query->have_posts()) {
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $template = get_post();
                $template_post_type = \get_field('post_type', $template->ID);
                if (!empty($post->post_type) && $template_post_type === $post->post_type) {
                    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
                    $is_block_editor = $screen ? $screen->is_block_editor() : false;
                    if ($is_block_editor) {
                        $content = get_the_content($template->ID);
                    }
                }
            }
            wp_reset_postdata();
        }

        return $content;
    }
}
