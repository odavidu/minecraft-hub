<?php

namespace core\npc\types;

use core\Nexus;
use core\NexusPlayer;
use core\npc\NPC;
use libs\utils\Utils;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Prison extends NPC {

    /**
     * Prison constructor.
     */
    public function __construct() {
        $path = DIRECTORY_SEPARATOR . "home" . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . "json" . DIRECTORY_SEPARATOR . "astronaut.json";
        $skin = Utils::createSkin(Utils::getSkinDataFromJSON($path));
        $position = new Position(-65, 52, 1, Server::getInstance()->getWorldManager()->getDefaultWorld());
        $nameTag = $this->updateNameTag();
        parent::__construct($skin, $position, $nameTag);
    }

    /**
     * @param Player $player
     */
    public function tick(Player $player): void {
        if($this->hasSpawnedTo($player)) {
            $this->setNameTag($player);
        }
    }

    /**
     * @return string
     */
    public function updateNameTag(): string {
        $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19100);
        if($server !== null and $server->isOnline()) {
            $amount = $server->getPlayers();
            $status = TextFormat::GRAY . $amount . TextFormat::GREEN . TextFormat::BOLD . " ONLINE";
        }
        else {
            $status = TextFormat::RED . TextFormat::BOLD . "OFFLINE";
        }
        $this->nameTag = TextFormat::YELLOW . "Prison" . TextFormat::RESET . "\n$status";
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function tap(Player $player): void {
        if($player instanceof NexusPlayer) {
            $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19100);
            Nexus::getInstance()->getServerManager()->addToQueue($player, $server->getIP(), $server->getPort());
        }
    }
}