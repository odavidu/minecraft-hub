<?php

declare(strict_types = 1);

namespace core\provider\task;

use core\Nexus;
use core\NexusPlayer;
use libs\utils\Task;
use pocketmine\utils\TextFormat;

class LoadScreenTask extends Task {

    /** @var NexusPlayer */
    private $player;

    /** @var int */
    private $time;

    /**
     * LoadScreenTask constructor.
     *
     * @param NexusPlayer $player
     */
    public function __construct(NexusPlayer $player) {
        $this->player = $player;
        $this->time = 30;
    }

    public function onRun(): void {
        if($this->player === null or ($this->player->isOnline() === false and $this->time !== 30)) {
            $this->cancel();
            return;
        }
        $this->player->testLoad();
        if($this->player->isLoaded() === true and $this->player->spawned === true) {
            $this->cancel();
            return;
        }
        if($this->time >= 0) {
            $this->time--;
            return;
        }
        $this->player->kick(TextFormat::RED . "Loading timed out. Rejoin to load again!", null);
    }
}
