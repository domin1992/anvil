<?php

function config($path)
{
    return (new \Anvil\Support\ConfigLoader)->load($path);
}

function env($key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

/**
 * Get user ip based on connecting browser
 */
if (!function_exists('gdpr_get_user_ip')) {
    function gdpr_get_user_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}

function is_login_page()
{
    return in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);
}

function nper($rate, $payment, $present)
{
    $num = $payment * (1 + $rate * 0) - 0 * $rate;
    $den = ($present * $rate + $payment * (1 + $rate * 0));

    return log10($num / $den) / log10(1 + $rate);
}

function zncr_pagination()
{
    global $wp_query;

    $pages = paginate_links(
        [
            'base' => str_replace(99999, '%#%', esc_url(get_pagenum_link(99999))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'mid_size' => 1,
            'prev_next' => false,
            'type' => 'array',
        ]
    );

    $prev_link = get_previous_posts_link('<svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.50049 0.72168L0.843634 6.37853L6.50049 12.0354" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>');

    $next_link = get_next_posts_link('<svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.50098 12.0354L11.1578 6.37855L5.50098 0.721692" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>');

    if (!count($pages)) {
        return '';
    }

    ob_start();

    get_template_part('partials/pagination', null, compact(
        'pages',
        'prev_link',
        'next_link'
    ));

    return ob_get_clean();
}

function viaManifest(string $src)
{
    $viteHelper = new \Anvil\Support\ViteHelper;

    return $viteHelper->viaManifest($src);
}
