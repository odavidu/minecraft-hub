<?php

namespace core\forms;

use core\Nexus;
use core\NexusPlayer;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TransferForm extends MenuForm {

    /**
     * TransferForm constructor.
     */
    public function __construct(NexusPlayer $player) {
        $options = [];
        $serverManager = Nexus::getInstance()->getServerManager();
        $server = $serverManager->getServer("hub.nexuspe.net", 19100);
        if($server !== null and $server->isOnline()) {
            $amount = $server->getPlayers();
            $status = TextFormat::GRAY . $amount . TextFormat::GREEN . TextFormat::BOLD . " ONLINE";
        }
        else {
            $status = TextFormat::RED . TextFormat::BOLD . "OFFLINE";
        }
        $options[] = new MenuOption("Prison $status");
        $server = $serverManager->getServer("hub.nexuspe.net", 19134);
        if($server !== null and $server->isOnline()) {
            $amount = $server->getPlayers();
            $status = TextFormat::GRAY . $amount . TextFormat::GREEN . TextFormat::BOLD . " ONLINE";
        }
        else {
            $status = TextFormat::RED . TextFormat::BOLD . "OFFLINE";
        }
        $options[] = new MenuOption("OP Faction $status");
        $server = $serverManager->getServer("hub.nexuspe.net", 19135);
        if($server !== null and $server->isOnline()) {
            $amount = $server->getPlayers();
            $status = TextFormat::GRAY . $amount . TextFormat::GREEN . TextFormat::BOLD . " ONLINE";
        }
        else {
            $status = TextFormat::RED . TextFormat::BOLD . "OFFLINE";
        }
        $options[] = new MenuOption("Events $status");
        if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $server = $serverManager->getServer("hub.nexuspe.net", 19123);
            if($server !== null and $server->isOnline()) {
                $amount = $server->getPlayers();
                $status = TextFormat::GRAY . $amount . TextFormat::GREEN . TextFormat::BOLD . " ONLINE";
            }
            else {
                $status = TextFormat::RED . TextFormat::BOLD . "OFFLINE";
            }
            $options[] = new MenuOption("Development $status");
        }
        parent::__construct(TextFormat::BOLD . TextFormat::AQUA . "Server Selector", "Which server would you like to play?", $options);
    }

    /**
     * @param NexusPlayer $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        switch($selectedOption) {
            case 0:
                $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19100);
                Nexus::getInstance()->getServerManager()->addToQueue($player, $server->getIP(), $server->getPort());
                return;
            case 1:
                $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19134);
                Nexus::getInstance()->getServerManager()->addToQueue($player, $server->getIP(), $server->getPort());
                return;
            case 2:
                $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19135);
                Nexus::getInstance()->getServerManager()->addToQueue($player, $server->getIP(), $server->getPort());
                return;
            case 3:
                $server = Nexus::getInstance()->getServerManager()->getServer("hub.nexuspe.net", 19123);
                Nexus::getInstance()->getServerManager()->addToQueue($player, $server->getIP(), $server->getPort());
                return;
        }
    }
}