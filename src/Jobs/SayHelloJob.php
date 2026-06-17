<?php

namespace Anvil\Jobs;

class SayHelloJob extends Job
{
    protected function setInterval()
    {
        $this->everyMinute();
    }

    public function handle()
    {
        echo 'Zncr says Hello!';
    }
}
