<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\NexusPlayer;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class UnlinkDiscordCommand extends Command {

    /**
     * UnlinkDiscordCommand constructor.
     */
    public function __construct() {
        parent::__construct("unlinkdiscord", "Unlink discord.", "/unlinkdiscord");
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
        if($sender->getDiscordId() === null) {
            $sender->sendMessage(TextFormat::RED . "You must link an account before you can unlink!");
            return;
        }
        $this->getCore()->getDiscordManager()->removeDiscordUser($sender->getDiscordId());
        $sender->setDiscordId(null);
        $sender->saveData();
        $sender->sendMessage(TextFormat::GREEN . "You have successfully unlinked your discord!");
    }
}