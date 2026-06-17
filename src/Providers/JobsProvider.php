<?php

namespace Anvil\Providers;

use Anvil\Jobs\Job;
use Anvil\Support\Discovery;

class JobsProvider implements Provider
{
    public function boot(): void
    {
        add_action('rest_api_init', [$this, 'restApiInit']);
    }

    public function restApiInit(): void
    {
        register_rest_route(
            'zncr',
            'cron-jobs',
            [
                'methods' => 'GET',
                'permission_callback' => '__return_true',
                'callback' => [$this, 'runCronJobs'],
            ]
        );
    }

    public function runCronJobs(): void
    {
        foreach (Discovery::classes('Jobs', Job::class) as $job) {
            $jobInstance = new $job;
            if ($jobInstance->shouldRun()) {
                $jobInstance->handle();
            }
        }
    }
}
