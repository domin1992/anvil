<?php

namespace Anvil\Actions;

class SaveParsedContentToMeta extends Action
{
    public string $hook = 'save_post';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle($post_id)
    {
        $content = get_the_content(null, false, $post_id);
        $blocks = parse_blocks($content);
        $content_html = '';

        foreach ($blocks as $block) {
            $content_html .= render_block($block);
        }

        update_post_meta($post_id, 'processed_content_html', $content_html);
        $content = strip_shortcodes($content);
        remove_filter('the_content', 'wptexturize');
        $content = apply_filters('the_content', $content);
        add_filter('the_content', 'wptexturize');
        $content = str_replace(']]>', ']]&gt;', $content);
        $content = wp_trim_words(strip_tags($content), PHP_INT_MAX - 1, '');
        update_post_meta($post_id, 'processed_content_text', $content);
    }
}
