<?php

namespace DesertDionysus\Inertia;

use Closure;

class Inertia
{
    protected static $url;

    protected static $props;

    protected static $request;

    protected static $version;

    protected static $component;

    protected static $shared_props = [];

    protected static $root_view = 'app.php';

    public static function render(string $component, array $props = [])
    {
        global $bb_inertia_page;

        self::setRequest();

        self::setUrl();
        self::setComponent($component);
        self::setProps($props);

        $bb_inertia_page = [
            'url'       => self::$url,
            'props'     => self::$props,
            'version'   => self::$version,
            'component' => self::$component,
        ];

        if (InertiaHeaders::inRequest()) {
            if (self::$version && (InertiaHeaders::get('X-Inertia-Version') !== (string) self::$version)) {
                self::abortConflict();
            }

            InertiaHeaders::addToResponse();

            wp_send_json($bb_inertia_page);
        }

        require_once get_stylesheet_directory() . '/' . self::$root_view;
    }

    protected static function abortConflict()
    {
        header('HTTP/1.1 409 Conflict');
        header('X-Inertia-Location: ' . self::$url);
        exit;
    }

    public static function setRootView(string $name)
    {
        self::$root_view = $name;
    }

    public static function version(?string $version = null)
    {
        if (is_null($version)) {
            return self::$version;
        }

        self::$version = $version;
    }

    public static function versionFromFile(string $path)
    {
        if (file_exists($path)) {
            self::version(md5_file($path));
        }
    }

    public static function versionFromVite(string $manifest_path)
    {
        if (file_exists($manifest_path)) {
            // Using the hash of the manifest file is a reliable way to detect changes.
            self::version(md5_file($manifest_path));
        }
    }

    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            self::$shared_props = array_merge(self::$shared_props, $key);
        } else {
            InertiaHelper::arraySet(self::$shared_props, $key, $value);
        }
    }

    public static function lazy(callable $callback)
    {
        return new LazyProp($callback);
    }

    public static function defer(callable $callback, ?string $group = null)
    {
        return new DeferredProp($callback, $group);
    }

    public static function merge(callable $callback)
    {
        return new MergeProp($callback);
    }

    public static function always($value)
    {
        return new AlwaysProp($value);
    }

    public static function location($url)
    {
        if (InertiaHeaders::inRequest()) {
            header('HTTP/1.1 409 Conflict');
            header('X-Inertia-Location: ' . $url);
            exit;
        }

        wp_redirect($url);
        exit;
    }

    protected static function setRequest()
    {
        global $wp;

        self::$request = array_merge([
            'WP-Inertia' => (array) $wp,
        ], InertiaHeaders::all());
    }

    protected static function setUrl()
    {
        self::$url = isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI']
            : '/';
    }

    protected static function setProps(array $props)
    {
        $props = array_merge($props, self::$shared_props);

        // Add WordPress Nonce
        if (! isset($props['nonce'])) {
            $props['nonce'] = wp_create_nonce('wp_rest');
        }

        // Add Flashed Messages
        if (! isset($props['flash'])) {
            $props['flash'] = Response::getFlashes();
        }

        // Add Validation Errors
        if (! isset($props['errors'])) {
            $props['errors'] = (object) Response::getErrors();
        }

        $partial_data = isset(self::$request['x-inertia-partial-data'])
            ? self::$request['x-inertia-partial-data']
            : '';

        $partial_except = isset(self::$request['x-inertia-partial-except'])
            ? self::$request['x-inertia-partial-except']
            : '';

        $only   = array_filter(explode(',', $partial_data));
        $except = array_filter(explode(',', $partial_except));

        $partial_component = isset(self::$request['x-inertia-partial-component'])
            ? self::$request['x-inertia-partial-component']
            : '';

        $is_partial = ($partial_component === self::$component);

        // Separate AlwaysProps early
        $always = [];
        foreach ($props as $key => $value) {
            if ($value instanceof AlwaysProp) {
                $always[$key] = $value();
                unset($props[$key]);
            }
        }

        if ($is_partial && $only) {
            $props = InertiaHelper::arrayOnly($props, $only);
        } elseif ($is_partial && $except) {
            $props = InertiaHelper::arrayExcept($props, $except);
        }

        // Re-merge AlwaysProps
        $props = array_merge($props, $always);

        // Evaluate callables if they are still in props (Shared Props or normal Closures)
        foreach ($props as $key => $value) {
            if (InertiaHelper::isCallable($value) && !($value instanceof DeferredProp || $value instanceof LazyProp || $value instanceof MergeProp || $value instanceof AlwaysProp)) {
                $props[$key] = $value();
            }
        }

        $deferred = [];

        foreach ($props as $key => $value) {
            if ($value instanceof DeferredProp) {
                if ($is_partial && $only && in_array($key, $only)) {
                    $props[$key] = $value();
                } elseif ($is_partial && $except && !in_array($key, $except)) {
                    $props[$key] = $value();
                } else {
                    $deferred[] = [
                        'key'   => $key,
                        'group' => $value->getGroup(),
                    ];

                    unset($props[$key]);
                }

                continue;
            }

            if ($value instanceof LazyProp) {
                if ($is_partial && $only && in_array($key, $only)) {
                    $props[$key] = $value();
                } elseif ($is_partial && $except && !in_array($key, $except)) {
                    $props[$key] = $value();
                } else {
                    unset($props[$key]);
                }

                continue;
            }

            if ($value instanceof MergeProp) {
                $props[$key] = $value();

                continue;
            }
        }

        self::$props = $props;

        if ($deferred) {
            global $bb_inertia_page;
            $bb_inertia_page['deferredProps'] = $deferred;
        }

        if ($is_partial && isset(self::$request['x-inertia-reset'])) {
            global $bb_inertia_page;
            $bb_inertia_page['clearHistory'] = true;
        }
    }

    protected static function setComponent(string $component)
    {
        self::$component = $component;
    }
}
