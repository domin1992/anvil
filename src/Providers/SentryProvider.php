<?php

namespace Anvil\Providers;

use Throwable;

class SentryProvider implements Provider
{
    public function boot(): void
    {
        if (!env('SENTRY_DSN')) {
            return;
        }

        \Sentry\init([
            'dsn' => env('SENTRY_DSN'),
            'environment' => env('APP_ENV', 'production'),
        ]);

        set_exception_handler(function (Throwable $e) {
            \Sentry\captureException($e);
        });

        register_shutdown_function(function () {
            $error = error_get_last();

            if ($error && in_array($error['type'], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
            ])) {
                \Sentry\captureMessage(
                    $error['message'],
                    \Sentry\Severity::fatal()
                );
            }
        });
    }
}
