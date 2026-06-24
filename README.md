# Coming Soon Symfony Bundle

A Symfony bundle that lets you **enable or disable a Coming Soon page** for your application with a single configuration flag.

## Features

- Toggle the coming soon page on/off via a single `enabled` flag
- Configurable HTTP status code (default: `503 Service Unavailable`)
- Customisable Twig template
- IP whitelist – allow specific IP addresses to bypass the coming soon page
- Excluded routes – allow specific named routes to pass through
- Excluded paths – allow specific URL path prefixes to pass through (e.g. `/admin`)

## Installation

```bash
composer require jack009/coming-soon-symfony-bundle
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Jack009\ComingSoonBundle\ComingSoonBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/coming_soon.yaml`:

```yaml
coming_soon:
    enabled: true                                      # set to false to disable
    template: '@ComingSoon/coming_soon.html.twig'      # optional, override the template
    status_code: 503                                   # optional, default 503
    whitelisted_ips:                                   # optional
        - 127.0.0.1
        - 192.168.1.100
    excluded_routes:                                   # optional
        - app_health_check
    excluded_paths:                                    # optional
        - /admin
```

### Options

| Option | Type | Default | Description |
|---|---|---|---|
| `enabled` | bool | `false` | Set to `true` to show the coming soon page |
| `template` | string | `@ComingSoon/coming_soon.html.twig` | Twig template to render |
| `status_code` | int | `503` | HTTP status code of the response |
| `whitelisted_ips` | string[] | `[]` | IPs that bypass the coming soon page |
| `excluded_routes` | string[] | `[]` | Named routes that bypass the coming soon page |
| `excluded_paths` | string[] | `[]` | URL path prefixes that bypass the coming soon page |

## Custom Template

Override the built-in template by pointing `template` at your own Twig file:

```yaml
coming_soon:
    enabled: true
    template: 'coming_soon/index.html.twig'
```

The template receives one variable:

| Variable | Type | Description |
|---|---|---|
| `status_code` | int | The configured HTTP status code |

## License

MIT
