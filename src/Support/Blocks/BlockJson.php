<?php

/**
 * Block Json
 */

namespace Anvil\Support\Blocks;

use Anvil\Exceptions\BlockAlreadyExistsException;
use Anvil\Misc\Tools;
use Anvil\Support\HasAccessToFilesystem;

/**
 * Block Json Class
 */
class BlockJson
{
    use HasAccessToFilesystem;

    /**
     * Instance of this singleton
     *
     * @var BlockJson
     */
    public static $instance = null;

    /**
     * Block Json File Path
     *
     * @var string
     */
    private $block_json_path = null;

    /**
     * Block Json File
     *
     * @var array
     */
    private $blocks = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initFilesystem();
        $this->loadJsonFile();
    }

    /**
     * Get instance
     *
     * @return BlockJson
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Load json file
     *
     * @return void
     */
    private function loadJsonFile()
    {
        $this->block_json_path = get_template_directory().'/app/blocks/blocks.json';

        if (!file_exists($this->block_json_path)) {
            return;
        }

        $this->blocks = [];

        foreach (json_decode($this->wpFilesystem->get_contents($this->block_json_path), true) as $block) {
            $this->blocks[] = BlockSchema::createFromArray($block);
        }
    }

    /**
     * Block exists
     *
     * @param  string  $block_name  Block name.
     * @return bool
     */
    public function block_exists($block_name)
    {
        foreach ($this->blocks as $block) {
            if (Tools::strSnake($block->name) === Tools::strSnake($block_name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add block to json
     *
     * @param  BlockSchema  $block_schema  Block Schema.
     * @return void
     *
     * @throws BlockAlreadyExistsException Block already exists.
     */
    public function add(BlockSchema $block_schema)
    {
        if ($this->block_exists($block_schema->name)) {
            throw new BlockAlreadyExistsException('Block with that name already exists!');
        }

        $this->blocks[] = $block_schema;
    }

    /**
     * Add and saves block to json
     *
     * @param  BlockSchema  $block_schema  Block Schema.
     * @return void
     */
    public function addAndSave(BlockSchema $block_schema)
    {
        $this->add($block_schema);
        $this->save();
    }

    /**
     * Save json file
     *
     * @return void
     */
    public function save()
    {
        if (!file_exists(dirname($this->block_json_path))) {
            mkdir(dirname($this->block_json_path), 0755, true);
        }

        $blocks_json = [];
        foreach ($this->blocks as $block) {
            $blocks_json[] = $block->to_array();
        }

        $this->wpFilesystem->put_contents($this->block_json_path, wp_json_encode($blocks_json, JSON_PRETTY_PRINT));
    }

    /**
     * Get blocks
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Static add block to json
     *
     * @param  BlockSchema  $block_schema  Block Schema.
     * @return void
     */
    public static function staticAdd(BlockSchema $block_schema)
    {
        self::getInstance()->add($block_schema);
    }

    /**
     * Static add and saves block to json
     *
     * @param  BlockSchema  $block_schema  Block Schema.
     * @return void
     */
    public static function staticAddAndSave(BlockSchema $block_schema)
    {
        self::getInstance()->addAndSave($block_schema);
    }

    /**
     * Static save json file
     *
     * @return void
     */
    public static function staticSave()
    {
        self::getInstance()->save();
    }

    /**
     * Static get blocks
     *
     * @return void
     */
    public static function staticGetBlocks()
    {
        self::getInstance()->getBlocks();
    }
}
