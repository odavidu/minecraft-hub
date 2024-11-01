<?php
declare(strict_types=1);

namespace core\command\types;

use core\command\utils\Command;
use core\NexusPlayer;
use libs\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class SaveSkinCommand extends Command {

    /**
     * SaveSkinCommand constructor.
     */
    public function __construct() {
        parent::__construct("saveskin", "Has a mysterious function, only could be executed by IsThatSwifty.", "/saveskin <skinName>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$sender instanceof NexusPlayer) {
            $sender->sendMessage(TextFormat::RED . "Insufficient permission!");
            return;
        }
        if(!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $sender->sendMessage(TextFormat::RED . "Insufficient permission!");
            return;
        }
        if($sender->getName() !== "IsThatSwifty") {
            $sender->sendMessage(TextFormat::RED . "You just got caught " . TextFormat::DARK_RED . "LACKING" . TextFormat::RED . ". Only someone under the username of " . TextFormat::YELLOW . "IsThatSwifty" . TextFormat::RED . " can use this command.");
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "Usage: " . TextFormat::RESET . TextFormat::WHITE . $this->getUsage());
            return;
        }
        $name = $args[0];
        $this->getCore()->saveResource("$name.json");
        $config = new Config($this->getCore()->getDataFolder() . "$name.json", Config::JSON);
        $config->set("skinData", Utils::createArrayFromSkin($sender->getSkin()));
        $config->save();
    }
}