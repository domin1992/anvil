<?php

namespace Anvil\Support\Jobs;

use Cron\CronExpression;

trait Interval
{
    public function at(string $expression): self
    {
        $this->execution_time = new CronExpression($expression);

        return $this;
    }

    public function date(string|\DateTime $date): self
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        $this->execution_year = $date->format('Y');

        return $this->at("{$date->format('i')} {$date->format('H')} {$date->format('d')} {$date->format('m')} *");
    }

    public function everyMinute(int|string|null $minute = null): self
    {
        $minute_expression = '*';
        if ($minute !== null) {
            $c = $this->validateCronSequence($minute);
            $minute_expression = '*/'.$c['minute'];
        }

        return $this->at($minute_expression.' * * * *');
    }

    public function hourly(int|string $minute = 0): self
    {
        $c = $this->validateCronSequence($minute);

        return $this->at("{$c['minute']} * * * *");
    }

    public function daily(int|string $hour = 0, int|string $minute = 0): self
    {
        if (is_string($hour)) {
            $parts = explode(':', $hour);
            $hour = $parts[0];
            $minute = isset($parts[1]) ? $parts[1] : '0';
        }
        $c = $this->validateCronSequence($minute, $hour);

        return $this->at("{$c['minute']} {$c['hour']} * * *");
    }

    public function weekly(int|string $weekday = 0, int|string $hour = 0, int|string $minute = 0): self
    {
        if (is_string($hour)) {
            $parts = explode(':', $hour);
            $hour = $parts[0];
            $minute = isset($parts[1]) ? $parts[1] : '0';
        }
        $c = $this->validateCronSequence($minute, $hour, null, null, $weekday);

        return $this->at("{$c['minute']} {$c['hour']} * * {$c['weekday']}");
    }

    public function monthly(int|string $month = '*', int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        if (is_string($hour)) {
            $parts = explode(':', $hour);
            $hour = $parts[0];
            $minute = isset($parts[1]) ? $parts[1] : '0';
        }
        $c = $this->validateCronSequence($minute, $hour, $day, $month);

        return $this->at("{$c['minute']} {$c['hour']} {$c['day']} {$c['month']} *");
    }

    public function sunday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(0, $hour, $minute);
    }

    public function monday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(1, $hour, $minute);
    }

    public function tuesday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(2, $hour, $minute);
    }

    public function wednesday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(3, $hour, $minute);
    }

    public function thursday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(4, $hour, $minute);
    }

    public function friday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(5, $hour, $minute);
    }

    public function saturday(int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->weekly(6, $hour, $minute);
    }

    public function january(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(1, $day, $hour, $minute);
    }

    public function february(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(2, $day, $hour, $minute);
    }

    public function march(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(3, $day, $hour, $minute);
    }

    public function april(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(4, $day, $hour, $minute);
    }

    public function may(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(5, $day, $hour, $minute);
    }

    public function june(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(6, $day, $hour, $minute);
    }

    public function july(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(7, $day, $hour, $minute);
    }

    public function august(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(8, $day, $hour, $minute);
    }

    public function september(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(9, $day, $hour, $minute);
    }

    public function october(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(10, $day, $hour, $minute);
    }

    public function november(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(11, $day, $hour, $minute);
    }

    public function december(int|string $day = 1, int|string $hour = 0, int|string $minute = 0): self
    {
        return $this->monthly(12, $day, $hour, $minute);
    }
}
