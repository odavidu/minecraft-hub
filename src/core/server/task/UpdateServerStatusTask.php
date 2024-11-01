<?php

declare(strict_types = 1);

namespace core\server\task;

use core\server\ServerManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class UpdateServerStatusTask extends Task {

    /** @var ServerManager */
    private $manager;

    /**
     * UpdateServerStatusTask constructor.
     *
     * @param ServerManager $manager
     */
    public function __construct(ServerManager $manager) {
        $this->manager = $manager;
    }

    public function onRun(): void {
        foreach($this->manager->getServers() as $server) {
            Server::getInstance()->getAsyncPool()->submitTaskToWorker(new GetStatusTask($server->getIP(), $server->getPort()), 3);
        }
    }
}
