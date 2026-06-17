<?php

namespace Anvil\Providers;

use Anvil\CustomPostTypes\CustomPostType;
use Anvil\Support\Discovery;

class CustomPostTypesProvider implements Provider
{
    public function boot(): void
    {
        foreach (Discovery::classes('CustomPostTypes', CustomPostType::class) as $customPostType) {
            $instance = new $customPostType;
            $instance->handle();
        }
    }
}
