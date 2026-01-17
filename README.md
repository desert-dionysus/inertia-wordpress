# Inertia.js WordPress Adapter (Modernized Fork)

A modernized [Inertia.js](https://inertiajs.com) server-side adapter for WordPress, aligned with Inertia.js v2.0+ protocol.

> **Note**: This is a fork of the excellent work by [Andrew Rhyand (BoxyBird)](https://github.com/boxybird/inertia-wordpress). While the core philosophy remains the same, this version has been modernized for PHP 8.2+ and adds support for the latest Inertia.js features like Deferred Props, Merge Props, and more.

## Installation

Install the package via composer:

```bash
composer require desert-dionysus/inertia-wordpress
```

## Acknowledgements & Resources

This project is a fork of `boxybird/inertia-wordpress`. We highly recommend checking out Andrew's original work and examples:
- **Original Repository**: [boxybird/inertia-wordpress](https://github.com/boxybird/inertia-wordpress)
- **Example Movie CPT Project**: [Theme Repository](https://github.com/boxybird/wordpress-inertia-demo-theme) | [Demo Site](https://wp-inertia.andrewrhyand.com)

## Root Template Example

> Location: `/wp-content/themes/your-theme/app.php` (or `layout.php`)

```php
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php wp_head(); ?>
    </head>
    <body>
        <?php bb_inject_inertia(); ?>
        <?php wp_footer(); ?>
    </body>
</html>
```

## The `inertia()` Helper

This fork introduces a global `inertia()` helper function that provides a fluent API similar to Laravel's adapter.

```php
// Render a component
return inertia('Index', ['posts' => $posts]);

// Chainable methods
return inertia()
    ->version('1.0.0')
    ->share('key', 'value')
    ->render('Index', $props);
```

## Core Features

### Inertia Responses

```php
use DesertDionysus\Inertia\Inertia;

// Using the Class
return Inertia::render('Posts/Index', [
    'posts' => $posts,
]);

// Or using the helper
return inertia('Posts/Index', [
    'posts' => $posts,
]);
```

### Shared Data

Shared data is automatically included in every Inertia response.

```php
inertia()->share('site_name', get_bloginfo('name'));

// Shared Closures are only executed if they are included in the response
inertia()->share('auth', function () {
    return is_user_logged_in() ? wp_get_current_user() : null;
});
```

### Always Props (v2.0)

Props wrapped in `always()` will be included in every response, even during partial reloads where they weren't specifically requested.

```php
return inertia('Profile', [
    'user' => $user,
    'social_links' => Inertia::always($links),
]);
```

### Deferred Props (v2.0)

Deferred props allow you to load heavy data asynchronously after the initial page load.

```php
return inertia('Dashboard', [
    'stats' => Inertia::defer(fn() => get_heavy_stats()),
    'logs'  => Inertia::defer(fn() => get_logs(), 'activity-group'),
]);
```

### Merge Props (v2.0)

Useful for infinite scrolling or pagination.

```php
return inertia('Blog', [
    'posts' => Inertia::merge(fn() => get_next_page_posts()),
]);
```

### External Redirects

Handles full-page redirects, even during Inertia AJAX requests.

```php
return Inertia::location('https://external-site.com');
```

## WordPress Integration

### Automatic Nonce Injection

This adapter automatically includes a `wp_rest` nonce in every response under the `nonce` prop, simplifying CSRF protection for your API calls.

### Flash Messages & Validation Errors

Easily pass flash messages or validation errors that will be automatically shared via the `flash` and `errors` props.

```php
// In your "Controller"
inertia()->flash('success', 'Post updated!');
inertia()->withErrors(['title' => 'Title is required']);

return Inertia::location(get_permalink($post_id));
```

### Asset Versioning

```php
// Automatically version from Vite manifest
Inertia::versionFromVite(get_stylesheet_directory() . '/dist/manifest.json');

// Or from any specific file
Inertia::versionFromFile(get_stylesheet_directory() . '/style.css');

// Or manually
Inertia::version('v1.2.3');
```

## Configuration

### Root Template File Override

```php
add_action('init', function () {
    Inertia::setRootView('layout.php');
});
```

---

## Inertia Docs

- [Links](https://inertiajs.com/links)
- [Pages](https://inertiajs.com/pages)
- [Requests](https://inertiajs.com/requests)
- [Shared Data](https://inertiajs.com/shared-data)
- [Asset Versioning](https://inertiajs.com/asset-versioning)
- [Partial Reloads](https://inertiajs.com/partial-reloads)
- [Deferred Props](https://inertiajs.com/deferred-props)
- [Merge Props](https://inertiajs.com/merge-props)
