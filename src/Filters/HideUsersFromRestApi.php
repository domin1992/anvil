<?php

namespace Anvil\Filters;

class HideUsersFromRestApi extends Filter
{
    public string|array $hook = 'rest_endpoints';

    public function handle($endpoints)
    {
        if (is_user_logged_in()) {
            return $endpoints;
        }

        foreach (['/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)'] as $route) {
            if (isset($endpoints[$route])) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }
}
