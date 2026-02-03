<?php
/**
 * Plugin Name: Headless Mode
 * Plugin URI:  https://github.com/roborracle/wp-headless-mode
 * Description: Locks down the WordPress front-end for headless CMS operation. Redirects all public requests to your front-end application while preserving wp-admin and REST API access.
 * Version:     1.0.0
 * Author:      Rob Orr
 * Author URI:  https://roborr.com
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Requires PHP: 7.4
 * Requires at least: 5.0
 *
 * Installation: Drop this file into wp-content/mu-plugins/
 * Must-use plugins load automatically and cannot be deactivated from the dashboard.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Derive the public front-end URL from the CMS URL.
 *
 * Strips the first subdomain prefix from the WordPress home URL.
 * Example: cms.example.com becomes example.com
 *
 * @return string The front-end URL without trailing slash.
 */
function headless_get_frontend_url(): string {
    $cms_url = defined('WP_HOME') ? WP_HOME : home_url();
    $parsed  = wp_parse_url($cms_url);
    $host    = $parsed['host'] ?? '';

    $parts         = explode('.', $host, 2);
    $frontend_host = count($parts) === 2 ? $parts[1] : $host;

    return ($parsed['scheme'] ?? 'https') . '://' . $frontend_host;
}

/**
 * Redirect all front-end template requests to the headless front-end.
 *
 * Preserves access to wp-admin, AJAX, cron, and REST API endpoints.
 */
add_action('template_redirect', function () {
    if (
        is_admin() ||
        wp_doing_ajax() ||
        wp_doing_cron() ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    $path = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'));
    wp_redirect(headless_get_frontend_url() . $path, 301);
    exit;
});

/**
 * Redirect author archive requests to prevent user enumeration.
 */
add_action('template_redirect', function () {
    if (is_author()) {
        wp_redirect(headless_get_frontend_url(), 301);
        exit;
    }
}, 1);

/**
 * Redirect all feed requests to the front-end feed URL.
 */
foreach (['do_feed', 'do_feed_rss2', 'do_feed_atom'] as $feed_hook) {
    add_action($feed_hook, function () {
        wp_redirect(headless_get_frontend_url() . '/feed', 301);
        exit;
    }, 1);
}

/**
 * Disable XML-RPC entirely. Common attack vector, not needed for headless.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove REST API user endpoints to prevent user enumeration.
 *
 * @param array $endpoints Registered REST API endpoints.
 * @return array Filtered endpoints.
 */
add_filter('rest_endpoints', function (array $endpoints): array {
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});

/**
 * Disable oEmbed discovery — not needed in headless mode.
 */
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

/**
 * Remove unnecessary meta tags from wp_head.
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
