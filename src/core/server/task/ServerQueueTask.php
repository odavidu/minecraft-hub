<?php

declare(strict_types = 1);

namespace core\server\task;

use core\server\ServerManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ServerQueueTask extends Task {

    /** @var ServerManager */
    private $manager;

    /** @var int */
    private $runsBeforeAlert = 20;

    /**
     * UpdateServerStatusTask constructor.
     *
     * @param ServerManager $manager
     */
    public function __construct(ServerManager $manager) {
        $this->manager = $manager;
    }

    public function onRun(): void {
        $this->runsBeforeAlert--;
        foreach($this->manager->getServers() as $server) {
            $queue = $this->manager->getQueue($server->getIP(), $server->getPort());
            if(empty($queue)) {
                continue;
            }
            if($server->isOnline()) {
                $player = array_shift($queue);
                if($player->isLoaded()) {
                    $player->transfer($server->getIP(), $server->getPort());
                }
            }
            $queue = $this->manager->getQueue($server->getIP(), $server->getPort());
            if((!empty($queue)) and $this->runsBeforeAlert <= 0) {
                $this->runsBeforeAlert = 20;
                foreach($queue as $index => $player) {
                    $place = $index + 1;
                    $player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "(!) " . TextFormat::RESET . TextFormat::YELLOW . "You are currently in " . TextFormat::BOLD . $place . TextFormat::RESET . TextFormat::YELLOW . "/" . count($queue) . " place in the {$server->getName()} join queue!");
                }
            }
        }
    }
}
