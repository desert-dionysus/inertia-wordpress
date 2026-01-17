<?php

namespace DesertDionysus\Inertia;

class AlwaysProp
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        if (is_callable($this->value)) {
            return call_user_func($this->value);
        }

        return $this->value;
    }
}
