<?php

declare(strict_types = 1);

namespace core\restart;

use core\Nexus;
use core\restart\task\RestartTask;

class RestartManager {

    /** @var Nexus */
    private $core;

    /**
     * NPCManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new RestartTask($core), 20);
    }
}