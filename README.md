# WP Headless Mode

A must-use WordPress plugin that locks down the front-end for headless CMS operation. All public-facing requests redirect to your front-end application while wp-admin and REST API access remain fully functional.

## What It Does

- Redirects all front-end page/post requests to your headless front-end (Next.js, Nuxt, Astro, etc.)
- Preserves wp-admin, REST API, AJAX, and WP-Cron access
- Disables XML-RPC (common brute-force attack vector)
- Blocks REST API user enumeration (`/wp/v2/users`)
- Blocks author archive enumeration (`?author=1`)
- Redirects RSS/Atom feeds to your front-end feed URL
- Removes unnecessary `wp_head` meta output and oEmbed discovery

## Requirements

- PHP 7.4+
- WordPress 5.0+

## Installation

1. Copy `headless-mode.php` into `wp-content/mu-plugins/`
2. If the `mu-plugins` directory doesn't exist, create it

That's it. Must-use plugins load automatically.

## Configuration

The plugin derives your front-end URL by stripping the first subdomain from your WordPress home URL:

| WP_HOME / home_url()        | Front-end URL             |
|------------------------------|---------------------------|
| `https://cms.example.com`    | `https://example.com`     |
| `https://wp.mysite.org`      | `https://mysite.org`      |
| `https://admin.app.io`       | `https://app.io`          |

Set `WP_HOME` in `wp-config.php` for explicit control:

```php
define('WP_HOME', 'https://cms.example.com');
define('WP_SITEURL', 'https://cms.example.com');
```

## What Stays Accessible

| Path | Status |
|------|--------|
| `/wp-admin/*` | Accessible |
| `/wp-login.php` | Accessible |
| `/wp-json/*` (REST API) | Accessible |
| `/wp-cron.php` | Accessible |
| AJAX requests | Accessible |
| Everything else | 301 redirect to front-end |

## Security Hardening

This plugin applies the following security measures automatically:

- **XML-RPC disabled** - Eliminates brute-force and DDoS amplification via `xmlrpc.php`
- **User enumeration blocked** - REST API `/wp/v2/users` endpoints removed, author archives redirected
- **Meta leakage removed** - WordPress version, RSD, WLW manifest, and shortlinks stripped from `wp_head`
- **oEmbed disabled** - Discovery links and host JS removed

## License

MIT - see [LICENSE](LICENSE)

## Author

[Robert David Orr](https://robertdavidorr.com)
