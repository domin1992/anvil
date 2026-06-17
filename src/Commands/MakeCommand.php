<?php

namespace Anvil\Commands;

use Anvil\Misc\Tools;
use Anvil\Support\Blocks\BlockMaker;
use Anvil\Support\Blocks\BlockSchema;
use Anvil\Support\HasAccessToFilesystem;

class MakeCommand
{
    use HasAccessToFilesystem;

    public static $name = 'make';

    /**
     * Creates action file.
     *
     * ## EXAMPLES
     *
     *     wp make action --name=MyAction --hook=some_wp_hook_name
     *
     * @when   after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function action($args, $assoc_args)
    {
        if (!isset($assoc_args['name']) || !$assoc_args['name']) {
            \WP_CLI::error('Please provide a name for the action.');

            return;
        }

        $this->initFilesystem();

        $file_content = $this->wpFilesystem->get_contents(__DIR__.'/stubs/Action.stub');

        $file_content = str_replace('DummyActionClass', $assoc_args['name'], $file_content);
        $file_content = str_replace(
            'DummyHookName',
            isset($assoc_args['hook']) && $assoc_args['hook']
                ? sprintf(" = '%s'", $assoc_args['hook'])
                : '',
            $file_content
        );

        $this->wpFilesystem->put_contents(
            get_template_directory().'/app/Actions/'.$assoc_args['name'].'.php',
            $file_content
        );

        Tools::composerDumpautoload();

        \WP_CLI::success(sprintf('Created %s class', $assoc_args['name']));
    }

    /**
     * Creates filter file.
     *
     * ## EXAMPLES
     *
     *     wp make filter --name=MyFilter --hook=some_wp_hook_name
     *
     * @when after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function filter($args, $assoc_args)
    {
        if (!isset($assoc_args['name']) || !$assoc_args['name']) {
            \WP_CLI::error('Please provide a name for the filter.');

            return;
        }

        $this->initFilesystem();

        $file_content = $this->wpFilesystem->get_contents(__DIR__.'/stubs/Filter.stub');

        $file_content = str_replace('DummyFilterClass', $assoc_args['name'], $file_content);
        $file_content = str_replace(
            'DummyHookName',
            isset($assoc_args['hook']) && $assoc_args['hook']
                ? sprintf(" = '%s'", $assoc_args['hook'])
                : '',
            $file_content
        );

        $this->wpFilesystem->put_contents(get_template_directory().'/app/Filters/'.$assoc_args['name'].'.php', $file_content);

        Tools::composerDumpautoload();

        \WP_CLI::success(sprintf('Created %s class', $assoc_args['name']));
    }

    /**
     * Creates enqueue file.
     *
     * ## EXAMPLES
     *
     *     wp make enqueue --name=MyEnqueue
     *
     * @when after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function enqueue($args, $assoc_args)
    {
        if (!isset($assoc_args['name']) || !$assoc_args['name']) {
            \WP_CLI::error('Please provide a name for the enqueue.');

            return;
        }

        $this->initFilesystem();

        $name_slug = Tools::strSlug($assoc_args['name']);
        $name_snake = Tools::strLower($assoc_args['name']);

        $file_content = $this->wpFilesystem->get_contents(__DIR__.'/stubs/Enqueue.stub');

        $file_content = str_replace('DummyEnqueueClass', $assoc_args['name'], $file_content);
        $file_content = str_replace('DummyEnqueueSlug', $name_slug, $file_content);
        $file_content = str_replace('DummyEnqueueSnake', $name_snake, $file_content);

        $this->wpFilesystem->put_contents(get_template_directory().'/app/Enqueue/'.$assoc_args['name'].'.php', $file_content);

        Tools::composerDumpautoload();

        \WP_CLI::success(sprintf('Created %s class', $assoc_args['name']));
    }

    /**
     * Creates block file.
     *
     * ## EXAMPLES
     *
     *     wp make block --name="Call To Action" --icon=welcome-widgets-menus --keywords="button,wide text" --type=full-width --styles --scripts --enable-assets-for-admin --mode=edit --example --align=left --post-types="post,page"
     *
     * @when after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function block($args, $assoc_args)
    {
        if (!isset($assoc_args['name']) || !$assoc_args['name']) {
            \WP_CLI::error('Please provide a name for the block.');

            return;
        }

        if (!isset($assoc_args['type']) || !$assoc_args['type']) {
            \WP_CLI::error('Please provide a type for the block.');

            return;
        }

        $block_maker = new BlockMaker;
        try {
            $block_maker->create(
                new BlockSchema(
                    $assoc_args['name'],
                    $assoc_args['type'],
                    isset($assoc_args['icon']) ? $assoc_args['icon'] : false,
                    isset($assoc_args['keywords']) ? explode(',', $assoc_args['keywords']) : [],
                    isset($assoc_args['styles']) && $assoc_args['styles'],
                    isset($assoc_args['scripts']) && $assoc_args['scripts'],
                    isset($assoc_args['enable-assets-for-admin']) && $assoc_args['enable-assets-for-admin'],
                    isset($assoc_args['mode']) && $assoc_args['mode'],
                    isset($assoc_args['example']),
                    isset($assoc_args['align']) && $assoc_args['align'],
                    isset($assoc_args['post-types']) && $assoc_args['post-types'] ? explode(',', $assoc_args['post-types']) : []
                )
            );
        } catch (\Anvil\Exceptions\BlockAlreadyExistsException $e) {
            \WP_CLI::error($e->getMessage());

            return;
        }

        Tools::composerDumpautoload();

        \WP_CLI::success(sprintf('Created %s class', $assoc_args['name']));
    }

    /**
     * Creates custom post type file.
     *
     * ## EXAMPLES
     *
     *     wp make cpt --name=Testimonials --singular=Testimonial --icon=dashicons-format-quote
     *
     * @when   after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function cpt($args, $assoc_args)
    {
        if (!isset($assoc_args['name']) || !$assoc_args['name']) {
            \WP_CLI::error('Please provide a name for the custom post type.');

            return;
        }

        $this->initFilesystem();

        $file_content = $this->wpFilesystem->get_contents(__DIR__.'/stubs/CustomPostType.stub');

        $file_content = str_replace('DummyCustomPostTypeName', $assoc_args['name'], $file_content);
        $file_content = str_replace(
            'DummyCustomPostTypeSlug',
            Tools::strSlug(
                Tools::strLower($assoc_args['name'])
            ),
            $file_content
        );
        $file_content = str_replace('DummyCustomPostTypeSingular', $assoc_args['singular'] ?? $assoc_args['name'], $file_content);
        $file_content = str_replace('DummyCustomPostTypeIcon', $assoc_args['icon'] ?? 'dashicons-format-quote', $file_content);

        $this->wpFilesystem->put_contents(get_template_directory().'/app/CustomPostTypes/'.$assoc_args['name'].'.php', $file_content);

        Tools::composerDumpautoload();

        \WP_CLI::success(sprintf('Created %s class', $assoc_args['name']));
    }
}
