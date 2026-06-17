<?php

namespace Anvil\Support\Blocks;

use Anvil\Misc\Tools;

class BlockSchema
{
    public function __construct(
        public string $name,
        public string $type,
        public string|false $icon = false,
        public array $keywords = [],
        public bool $styles = false,
        public bool $scripts = false,
        public bool $enable_assets_for_admin = false,
        public ?string $mode = null,
        public bool $example = false,
        public string|false $align = false,
        public array $post_types = []
    ) {}

    public function nameSlug()
    {
        return Tools::strSlug($this->name);
    }

    public function nameSnake()
    {
        return Tools::strSlug($this->name, '_', false);
    }

    public function typeSnake()
    {
        return Tools::strSlug($this->type, '_');
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'name_slug' => $this->nameSlug(),
            'name_snake' => $this->nameSnake(),
            'type' => $this->type,
            'icon' => $this->icon,
            'keywords' => $this->keywords,
            'styles' => $this->styles,
            'scripts' => $this->scripts,
            'enable_assets_for_admin' => $this->enable_assets_for_admin,
            'mode' => $this->mode,
            'example' => $this->example,
            'align' => $this->align,
            'post_types' => $this->post_types,
        ];
    }

    public function toJson()
    {
        $data = [
            'name' => get_template() . '/' . $this->nameSlug(),
            'title' => $this->name,
            'description' => $this->name,
            'category' => $this->type,
            'icon' => $this->icon,
            'apiVersion' => 2,
            'keywords' => $this->keywords,
            'acf' => [
                'mode' => 'edit',
                'renderTemplate' => sprintf('%s.php', $this->nameSlug()),
            ],
            'supports' => [
                'anchor' => true,
            ],
        ];

        if ($this->styles) {
            $data['style'] = sprintf('%s-css', $this->nameSlug());
        }

        if ($this->scripts) {
            $data['viewScript'] = sprintf('%s-js', $this->nameSlug());
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function createFromArray($array)
    {
        return new BlockSchema(
            isset($array['name']) ? $array['name'] : '',
            isset($array['type']) ? $array['type'] : '',
            isset($array['icon']) ? $array['icon'] : false,
            isset($array['keywords']) ? $array['keywords'] : [],
            isset($array['styles']) ? $array['styles'] : false,
            isset($array['scripts']) ? $array['scripts'] : false,
            isset($array['enable_assets_for_admin']) ? $array['enable_assets_for_admin'] : false,
            isset($array['mode']) ? $array['mode'] : null,
            isset($array['example']) ? $array['example'] : false,
            isset($array['align']) ? $array['align'] : false,
            isset($array['post_types']) ? $array['post_types'] : []
        );
    }
}
