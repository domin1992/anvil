<?php

namespace Anvil\Support;

use Closure;

class RestApiRoute
{
    public static function make(
        string $method,
        string $endpoint,
        Closure $callback,
        string $permission_callback = '__return_true'
    ): self {
        return new self(
            $method,
            $endpoint,
            $callback,
            $permission_callback
        );
    }

    public function __construct(
        public string $method,
        public string $endpoint,
        public Closure $callback,
        public string $permission_callback
    ) {
        //
    }
}
