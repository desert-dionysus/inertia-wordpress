<?php

namespace DesertDionysus\Inertia;

class InertiaHeaders
{
    public static function all()
    {
        return array_change_key_case(getallheaders(), CASE_LOWER);
    }

    public static function get($key)
    {
        $headers = self::all();

        return isset($headers[strtolower($key)]) ? $headers[strtolower($key)] : null;
    }

    public static function inRequest()
    {
        return self::get('x-inertia') === 'true';
    }

    public static function addToResponse()
    {
        header('Vary: Accept');
        header('X-Inertia: true');
    }
}
