<?php

declare(strict_types = 1);

namespace core\restart\task;

use core\Nexus;
use core\NexusPlayer;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class RestartTask extends Task {

    /** @var Nexus */
    private $core;

    /** @var int */
    private $time = 43200;

    /**
     * RestartTask constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    public function onRun(): void {
        $hours = floor($this->time / 3600);
        $minutes = floor(($this->time / 60) % 60);
        $seconds = $this->time % 60;
        if($hours == 0) {
            if($minutes == 0 and $seconds == 0) {
                foreach($this->core->getServer()->getOnlinePlayers() as $player) {
                    if(!$player instanceof NexusPlayer) {
                        continue;
                    }
                    $player->kick(TextFormat::RESET . TextFormat::RED . "Server is restarting...", "");
                }
                $this->core->getServer()->shutdown();
            }
        }
        $this->time--;
    }
}