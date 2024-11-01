<?php

declare(strict_types = 1);

namespace core\npc\task;

use core\npc\NPCManager;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NPCHeartbeatTask extends Task {

    /** @var NPCManager */
    private $manager;

    /** @var Player[] */
    private $players;

    /**
     * NPCHeartbeatTask constructor.
     *
     * @param NPCManager $manager
     */
    public function __construct(NPCManager $manager) {
        $this->manager = $manager;
        $this->players = Server::getInstance()->getOnlinePlayers();
    }

    public function onRun(): void {
        if(empty($this->players)) {
            $this->players = Server::getInstance()->getOnlinePlayers();
            if(empty($this->players)) {
                return;
            }
            foreach($this->manager->getNPCs() as $npc) {
                $npc->updateNameTag();
            }
            return;
        }
        $player = array_shift($this->players);
        if(!$player instanceof Player) {
            return;
        }
        if($player->isOnline() === false) {
            return;
        }
        foreach($this->manager->getNPCs() as $npc) {
            $npc->tick($player);
        }
    }
}
