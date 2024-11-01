<?php

declare(strict_types = 1);

namespace core\update;

use core\Nexus;
use core\update\task\CheckDiscordIDTask;
use core\update\task\UpdateHeartbeatTask;

class UpdateManager {

    /** @var Nexus */
    private $core;

    /**
     * NPCManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new UpdateHeartbeatTask($this), 200);
        $core->getScheduler()->scheduleRepeatingTask(new CheckDiscordIDTask(), 20);
    }
}