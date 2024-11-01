<?php

declare(strict_types = 1);

namespace core\update\task;

use core\EventListener;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ResetPlayersTask extends Task {

    /** @var EventListener */
    private $listener;

    /**
     * ResetPlayersTask constructor.
     *
     * @param EventListener $listener
     */
    public function __construct(EventListener $listener) {
        $this->listener = $listener;
    }

    public function onRun(): void {
        foreach($this->listener->players as $id => $value) {
            if(Server::getInstance()->getPlayerByRawUUID($id) === null) {
                unset($this->listener->players[$id]);
            }
        }
    }
}
