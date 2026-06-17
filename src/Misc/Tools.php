<?php

namespace Anvil\Misc;

class Tools
{
    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @source https://github.com/laravel/helpers
     *
     * @param  string  $title  Title.
     * @param  string  $separator  Separator.
     */
    public static function strSlug(string $title, string $separator = '-'): string
    {
        // Convert all dashes/underscores into separator.
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('!['.preg_quote($flip, null).']+!u', $separator, $title);

        // Replace @ with the word 'at'.
        $title = str_replace('@', $separator.'at'.$separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^'.preg_quote($separator, null).'\pL\pN\s]+!u', '', self::strLower($title));

        // Replace all separator characters and whitespace by a single separator.
        $title = preg_replace('!['.preg_quote($separator, null).'\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Convert a string to snake case.
     *
     * @source https://github.com/laravel/helpers
     *
     * @param  string  $value  Value.
     * @param  string  $delimiter  Delimiter.
     * @param  bool  $lower  Lowercase.
     * @return string
     */
    public static function strSnake(string $value, string $delimiter = '_', bool $lower = true)
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = preg_replace('/-/u', '', $value);

            $value = $lower
                ? self::strLower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value))
                : preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value);
        }

        return $value;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @source https://github.com/laravel/helpers
     *
     * @param  string  $value  Value.
     * @return string
     */
    public static function strLower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Get the slug of current post
     *
     * @return string
     */
    public static function getTheSlug()
    {
        global $post;

        if (is_single() || is_page()) {
            return $post->post_name;
        }

        return '';
    }

    /**
     * Limit the number of characters in a string.
     *
     * @source https://github.com/laravel/helpers
     *
     * @param  string  $value  Value to cut.
     * @param  int  $limit  Amount of characters to limit.
     * @param  string  $end  End of string.
     * @return string
     */
    public static function strLimit($value, $limit = 100, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * String starts with
     *
     * @param  mixed  $heystack  Heystack to check.
     * @param  mixed  $needle  String that you are looking for.
     * @return bool
     */
    public static function strStartsWith($heystack, $needle)
    {
        return strncmp($heystack, $needle, strlen($needle)) === 0;
    }

    /**
     * Ensure that SVG file has unique IDs inside their markup
     *
     * example usage:
     * carefully_insert_svg(wp_get_attachment_image_url($svg_file['id']))
     *
     * @param  string  $url  URL of svg file.
     * @return string
     */
    public static function carefullyInsertSvg($url)
    {
        $content = file_get_contents($url);
        $ids_pattern = "/#[^0-9ABCDEF][a-z0-9_\-]{1,}/m"; // Match all strings that starts with # and any word (with optional digits further) longer than 1 character
        preg_match_all($ids_pattern, $content, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $uuid = wp_generate_uuid4();
            $hashless_string = str_replace('#', '', $match[0]);
            $content = str_replace($hashless_string, 'svg_'.$uuid.'_'.$hashless_string, $content);
        }

        return $content;
    }

    /**
     * Composer dumpautoload
     *
     * Refreshes classmap of composer autoloader.
     *
     * @return void
     */
    public static function composerDumpautoload()
    {
        exec(
            sprintf(
                'cd %s && composer dumpautoload',
                get_template_directory()
            )
        );
    }

    /**
     * Check if string is valid json
     *
     * @param  string  $string
     * @return bool
     */
    public static function isJson($string)
    {
        return is_string($string)
            && is_array(json_decode($string, true))
            && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
