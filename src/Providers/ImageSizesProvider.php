<?php

namespace Anvil\Providers;

class ImageSizesProvider implements Provider
{
    public function boot(): void
    {
        $this->addImageSizes();
        $this->setPostThumbnailSize();
    }

    public function addImageSizes(): void
    {
        $image_sizes = config('image-sizes.image-sizes');
        if (is_array($image_sizes)) {
            foreach ($image_sizes as $image_size) {
                add_image_size($image_size['name'], $image_size['width'], $image_size['height'], $image_size['crop']);
            }
        }
    }

    public function setPostThumbnailSize(): void
    {
        $post_thumbnail_size = config('image-sizes.post-thumbnail-size');
        if (is_array($post_thumbnail_size)) {
            set_post_thumbnail_size($post_thumbnail_size['width'], $post_thumbnail_size['height'], $post_thumbnail_size['crop']);
        }
    }
}
