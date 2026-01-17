<?php

namespace DesertDionysus\Inertia;

class Response
{
    protected static $flash_key = 'inertia_flash';

    protected static $errors_key = 'inertia_errors';

    /**
     * Flash a message to the session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function flash(string $key, $value)
    {
        if (!session_id()) {
            session_start();
        }

        $_SESSION[self::$flash_key][$key] = $value;
    }

    /**
     * Get all flashed messages and clear them.
     *
     * @return array
     */
    public static function getFlashes(): array
    {
        if (!session_id()) {
            session_start();
        }

        $flashes = isset($_SESSION[self::$flash_key]) ? $_SESSION[self::$flash_key] : [];

        unset($_SESSION[self::$flash_key]);

        return $flashes;
    }

    /**
     * Set validation errors to the session.
     *
     * @param array $errors
     * @return void
     */
    public static function withErrors(array $errors)
    {
        if (!session_id()) {
            session_start();
        }

        $_SESSION[self::$errors_key] = $errors;
    }

    /**
     * Get all validation errors and clear them.
     *
     * @return array
     */
    public static function getErrors(): array
    {
        if (!session_id()) {
            session_start();
        }

        $errors = isset($_SESSION[self::$errors_key]) ? $_SESSION[self::$errors_key] : [];

        unset($_SESSION[self::$errors_key]);

        return $errors;
    }
}
