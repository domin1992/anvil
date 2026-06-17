<?php

namespace Anvil\Actions;

class FaduplaGetAttachmentUrlMediaLibrary extends Action
{
    public string $hook = 'wp_ajax_svg_get_attachment_url';

    public int $priority = 10;

    public int $accepted_args = 1;

    public function handle()
    {
        $url = '';
        $attachmentID = isset($_REQUEST['attachmentID'])
            ? filter_var($_REQUEST['attachmentID'], FILTER_SANITIZE_STRING)
            : '';

        if ($attachmentID) {
            $url = wp_get_attachment_url($attachmentID);
        }

        echo $url;
        exit();
    }
}
