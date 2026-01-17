<?php

namespace DesertDionysus\Inertia;

class DeferredProp
{
    /** @var callable */
    protected $callback;

    /** @var string|null */
    protected $group;

    public function __construct(callable $callback, ?string $group = null)
    {
        $this->callback = $callback;
        $this->group    = $group;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function __invoke()
    {
        return ($this->callback)();
    }
}
