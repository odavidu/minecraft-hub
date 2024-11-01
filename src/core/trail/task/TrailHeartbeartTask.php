<?php

declare(strict_types = 1);

namespace core\trail\task;

use core\Nexus;
use core\NexusPlayer;
use pocketmine\scheduler\Task;

class TrailHeartbeartTask extends Task {

    /** @var Nexus */
    private $core;

    /**
     * RestartTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    public function onRun(): void {
        foreach($this->core->getServer()->getOnlinePlayers() as $player) {
            if(!$player instanceof NexusPlayer) {
                continue;
            }
            $trail = $player->getTrail();
            if($trail !== null) {
                $trail->tick();
            }
        }
    }
}