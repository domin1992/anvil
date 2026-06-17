<?php

namespace Anvil\Support\Blocks;

use Anvil\Exceptions\BlockAlreadyExistsException;
use Anvil\Support\HasAccessToFilesystem;

class BlockMaker
{
    use HasAccessToFilesystem;

    public function __construct()
    {
        $this->initFilesystem();
    }

    public function create(BlockSchema $blockSchema)
    {
        if ($this->doesBlockAlreadyExist($blockSchema->nameSlug())) {
            throw new BlockAlreadyExistsException;
        }

        mkdir($this->blocksDir().'/'.$blockSchema->nameSlug());

        $this->createBlockJson($blockSchema);

        $this->createBlockView($blockSchema);

        if ($blockSchema->styles) {
            $this->createBlockStyles($blockSchema);
        }

        if ($blockSchema->scripts) {
            $this->createBlockScripts($blockSchema);
            $this->createBlockAsset($blockSchema);
        }
    }

    private function doesBlockAlreadyExist($slug)
    {
        if (file_exists($this->blockDir($slug))) {
            return true;
        }

        return false;
    }

    private function createBlockJson(BlockSchema $blockSchema)
    {
        $this->wpFilesystem->put_contents(
            sprintf('%s/block.json', $this->blockDir($blockSchema->nameSlug())),
            $blockSchema->toJson()
        );
    }

    private function createBlockView(BlockSchema $blockSchema)
    {
        $file_content = $this->wpFilesystem->get_contents(
            sprintf(
                '%s/stubs/block-view-%s.stub',
                __DIR__,
                $blockSchema->type
            )
        );

        $file_content = str_replace('DummyBlockName', $blockSchema->name, $file_content);
        $file_content = str_replace('DummyBlockSlug', $blockSchema->nameSlug(), $file_content);

        $file_path = sprintf(
            '%s/%s.php',
            $this->blockDir($blockSchema->nameSlug()),
            $blockSchema->nameSlug()
        );

        if (!file_exists($file_path)) {
            $this->wpFilesystem->put_contents(
                $file_path,
                $file_content
            );
        }
    }

    private function createBlockStyles(BlockSchema $blockSchema)
    {
        $file_content = $this->wpFilesystem->get_contents(
            sprintf(
                '%s/stubs/block-styles.stub',
                __DIR__
            )
        );

        $file_content = str_replace('DummyBlockName', $blockSchema->name, $file_content);

        $file_path = sprintf(
            '%s/%s.css',
            $this->blockDir($blockSchema->nameSlug()),
            $blockSchema->nameSlug()
        );

        if (!file_exists($file_path)) {
            $this->wpFilesystem->put_contents(
                $file_path,
                $file_content
            );
        }
    }

    private function createBlockScripts(BlockSchema $blockSchema)
    {
        $file_content = $this->wpFilesystem->get_contents(
            sprintf(
                '%s/stubs/block-scripts.stub',
                __DIR__
            )
        );

        $file_content = str_replace('DummyBlockName', $blockSchema->name, $file_content);

        $file_path = sprintf(
            '%s/%s.ts',
            $this->blockDir($blockSchema->nameSlug()),
            $blockSchema->nameSlug()
        );

        if (!file_exists($file_path)) {
            $this->wpFilesystem->put_contents(
                $file_path,
                $file_content
            );
        }
    }

    private function createBlockAsset(BlockSchema $blockSchema)
    {
        $file_content = $this->wpFilesystem->get_contents(
            sprintf(
                '%s/stubs/block-asset.stub',
                __DIR__
            )
        );

        $file_content = str_replace('DummyBlockSlug', $blockSchema->nameSlug(), $file_content);

        $file_path = sprintf(
            '%s/%s.asset.php',
            $this->blockDir($blockSchema->nameSlug()),
            $blockSchema->nameSlug()
        );

        if (!file_exists($file_path)) {
            $this->wpFilesystem->put_contents(
                $file_path,
                $file_content
            );
        }
    }

    private function blocksDir()
    {
        return sprintf('%s/blocks', get_template_directory());
    }

    private function blockDir(string $slug)
    {
        return sprintf('%s/%s', $this->blocksDir(), $slug);
    }
}
