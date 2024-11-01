<?php

namespace core\trail;

use core\NexusPlayer;

abstract class Trail {

    /** @var NexusPlayer */
    protected $owner;

    /**
     * Trail constructor.
     *
     * @param NexusPlayer $owner
     */
    public function __construct(NexusPlayer $owner) {
        $this->owner = $owner;
    }

    abstract public function tick(): void;
}