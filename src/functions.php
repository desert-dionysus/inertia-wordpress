<?php

use DesertDionysus\Inertia\Inertia;

if (!function_exists('inertia')) {
    function inertia(string $component = '', array $props = [])
    {
        if ($component) {
            return Inertia::render($component, $props);
        }

        return new class {
            public function render(string $component, array $props = [])
            {
                return Inertia::render($component, $props);
            }

            public function share($key, $value = null)
            {
                return Inertia::share($key, $value);
            }

            public function lazy(callable $callback)
            {
                return Inertia::lazy($callback);
            }

            public function defer(callable $callback, ?string $group = null)
            {
                return Inertia::defer($callback, $group);
            }

            public function merge(callable $callback)
            {
                return Inertia::merge($callback);
            }

            public function always($value)
            {
                return Inertia::always($value);
            }

            public function location(string $url)
            {
                return Inertia::location($url);
            }

            public function version(string $version = '')
            {
                return Inertia::version($version);
            }

            public function versionFromFile(string $path)
            {
                return Inertia::versionFromFile($path);
            }

            public function versionFromVite(string $manifest_path)
            {
                return Inertia::versionFromVite($manifest_path);
            }

            public function setRootView(string $name)
            {
                return Inertia::setRootView($name);
            }

            public function flash(string $key, $value)
            {
                return \DesertDionysus\Inertia\Response::flash($key, $value);
            }

            public function withErrors(array $errors)
            {
                return \DesertDionysus\Inertia\Response::withErrors($errors);
            }
        };
    }
}

if (!function_exists('bb_inject_inertia')) {
    function bb_inject_inertia(string $id = 'app', string $classes = '')
    {
        global $bb_inertia_page;

        if (!isset($bb_inertia_page)) {
            return;
        }

        $classes = !empty($classes)
            ? 'class="' . $classes . '"'
            : '';

        $page = htmlspecialchars(
            json_encode($bb_inertia_page),
            ENT_QUOTES,
            'UTF-8',
            true
        );

        echo "<div id=\"{$id}\" {$classes} data-page=\"{$page}\"></div>";
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}
