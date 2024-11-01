<?php

declare(strict_types = 1);

namespace core\server;

use core\Nexus;
use core\NexusPlayer;
use core\server\task\ServerQueueTask;
use core\server\task\UpdateServerStatusTask;
use pocketmine\utils\TextFormat;

class ServerManager {

    /** @var Nexus */
    private $core;

    /** @var Server[] */
    private $servers = [];

    /** @var NexusPlayer[][] */
    private $queue = [];

    /**
     * NPCManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new UpdateServerStatusTask($this), 200);
        $core->getScheduler()->scheduleRepeatingTask(new ServerQueueTask($this), 5);
        $this->init();
    }

    public function init() {
        $this->addServer(new Server("OP Faction", "hub.nexuspe.net", 19134));
        $this->addServer(new Server("Practice PvP", "hub.nexuspe.net", 19136));
        $this->addServer(new Server("Prison", "hub.nexuspe.net", 19100));
        $this->addServer(new Server("Events", "hub.nexuspe.net", 19135));
        $this->addServer(new Server("Development", "hub.nexuspe.net", 19123));
        //$this->addServer(new Events());
    }

    /**
     * @return Server[]
     */
    public function getServers(): array {
        return $this->servers;
    }

    /**
     * @param string $ip
     * @param int $port
     *
     * @return Server|null
     */
    public function getServer(string $ip, int $port): ?Server {
        foreach($this->servers as $server) {
            if($ip === $server->getIP() and $port === $server->getPort()) {
                return $server;
            }
        }
        return null;
    }

    /**
     * @param Server $server
     */
    public function addServer(Server $server): void {
        $this->servers[] = $server;
    }

    /**
     * @param string $ip
     * @param int $port
     *
     * @return NexusPlayer[]
     */
    public function getQueue(string $ip, int $port): array {
        $priority = [];
        $index = "$ip:$port";
        $queue = $this->queue[$index] ?? [];
        foreach($queue as $player) {
            $priority[$player->getImportance()] = $player;
        }
        rsort($priority);
        return $priority;
    }

    /**
     * @param NexusPlayer $player
     * @param string $ip
     * @param int $port
     */
    public function addToQueue(NexusPlayer $player, string $ip, int $port): void {
        $index = "$ip:$port";
        $this->queue[$index][] = $player;
        $queue = $this->getQueue($ip, $port);
        $place = array_search($player, $queue) + 1;
        $server = Nexus::getInstance()->getServerManager()->getServer($ip, $port);
        $player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "(!) " . TextFormat::RESET . TextFormat::YELLOW . "You are currently in " . TextFormat::BOLD . $place . TextFormat::RESET . TextFormat::YELLOW . "/" . count($queue) . " place in the {$server->getName()} join queue!");
    }

    /**
     * @param NexusPlayer $player
     */
    public function removeFromQueue(NexusPlayer $player): void {
        foreach($this->queue as $location => $players) {
            foreach($players as $index => $p) {
                if($p->getName() === $player->getName()) {
                    unset($this->queue[$location][$index]);
                }
            }
        }
    }
}