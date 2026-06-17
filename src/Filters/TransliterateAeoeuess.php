<?php

namespace Anvil\Filters;

class TransliterateAeoeuess extends Filter
{
    public string|array $hook = 'sanitize_title';

    public int $priority = 5;

    public int $accepted_args = 3;

    public function handle(string $title, ?string $raw_title, string $context): string
    {
        if ($raw_title != null) {
            $title = $raw_title;
        }
        $title = str_replace('Ä', 'ae', $title);
        $title = str_replace('ä', 'ae', $title);
        $title = str_replace('Ö', 'oe', $title);
        $title = str_replace('ö', 'oe', $title);
        $title = str_replace('Ü', 'ue', $title);
        $title = str_replace('ü', 'ue', $title);
        $title = str_replace('ẞ', 'ss', $title);
        $title = str_replace('ß', 'ss', $title);
        if ($context == 'save') {
            $title = remove_accents($title);
        }

        return $title;
    }
}
