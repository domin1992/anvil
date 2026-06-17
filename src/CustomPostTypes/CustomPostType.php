<?php

namespace Anvil\CustomPostTypes;

abstract class CustomPostType
{
    public $slug;

    public function config()
    {
        return [];
    }

    public function registered() {}

    /**
     * Handle
     *
     * @throws \Exception When config array is empty.
     * @throws \Exception When slug not provided.
     */
    public function handle(): void
    {
        if (!count($this->config())) {
            throw new \Exception('Config is empty in '.get_class($this));
        }

        if (!$this->slug) {
            throw new \Exception('Slug not provided in '.get_class($this));
        }

        add_action(
            'init',
            function () {
                register_post_type($this->slug, $this->config());
            }
        );

        $this->registered();
    }
}
