<?php

namespace Anvil\Filters;

class YoutubeNocookieOembed extends Filter
{
    public string|array $hook = 'oembed_dataparse';

    public int $priority = 10;

    public int $accepted_args = 3;

    public function handle($return, $data, $url)
    {
        return str_replace('youtube', 'youtube-nocookie', $return);
    }
}
