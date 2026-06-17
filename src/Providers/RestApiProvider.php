<?php

namespace Anvil\Providers;

use Anvil\Support\Discovery;

class RestApiProvider implements Provider
{
    public function boot(): void
    {
        add_action('rest_api_init', [$this, 'restApiInit']);
    }

    public function restApiInit(): void
    {
        foreach ($this->routeGroups() as $route_group) {
            foreach (include $route_group['routes'] as $route) {
                register_rest_route(
                    $route_group['namespace'],
                    $route->endpoint,
                    [
                        'methods' => $route->method,
                        'permission_callback' => $route->permission_callback,
                        'callback' => [new $route->callback[0], $route->callback[1]],
                    ]
                );
            }
        }
    }

    /**
     * Route files from the package + theme `RestApiRoutes` directories. The
     * REST namespace is derived from the file name, e.g. `zncr.php` => `zncr/v1`.
     *
     * @return array<int, array{namespace: string, routes: string}>
     */
    protected function routeGroups(): array
    {
        $groups = [];

        foreach (Discovery::files('RestApiRoutes') as $file) {
            $groups[] = [
                'namespace' => basename($file, '.php').'/v1',
                'routes' => $file,
            ];
        }

        return $groups;
    }
}
