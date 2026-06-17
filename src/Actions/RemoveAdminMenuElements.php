<?php

namespace Anvil\Actions;

class RemoveAdminMenuElements extends Action
{
    public string $hook = 'admin_menu';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        remove_menu_page('edit-comments.php');
    }
}
