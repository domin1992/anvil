<?php

namespace Anvil\Actions;

class RemoveComments extends Action
{
    public string $hook = 'admin_init';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        global $pagenow;

        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }

        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }

        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }
}
