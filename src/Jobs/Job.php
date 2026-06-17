<?php

namespace Anvil\Jobs;

use Anvil\Support\Jobs\Interval;

abstract class Job
{
    use Interval;

    protected $execution_time = null;

    public function __construct()
    {
        $this->setInterval();
    }

    protected function setInterval()
    {
        $this->everyMinute();
    }

    public function shouldRun()
    {
        if ($this->execution_time === null) {
            return false;
        }

        return $this->execution_time->isDue();
    }

    public function handle() {}
}
