<?php

declare(strict_types = 1);

namespace core\command\types;

use core\command\utils\Command;
use core\NexusPlayer;
use core\provider\SocketRequests;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class LinkDiscordCommand extends Command {

    /**
     * LinkDiscordCommand constructor.
     */
    public function __construct() {
        parent::__construct("linkdiscord", "Link discord.", "/linkdiscord <id>");
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
        if(!isset($args[0])) {
            $sender->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "Usage: " . TextFormat::RESET . TextFormat::WHITE . $this->getUsage());
            return;
        }
        $manager = $this->getCore()->getDiscordManager();
        if($sender->getDiscordId() !== null) {
            $user = $manager->getDiscordUser($sender->getDiscordId());
            $sender->sendMessage(TextFormat::YELLOW . "You are currently linked to: " . TextFormat::WHITE . $user->getTag());
            $sender->sendMessage(TextFormat::YELLOW . "Unlink your Discord account by executing " . TextFormat::AQUA . "/unlinkdiscord");
            return;
        }
        if(SocketRequests::hasOutgoingRequest(SocketRequests::CONFIRMATION_REQUEST, $sender)) {
            $sender->sendMessage(TextFormat::RED . "You already have an outgoing request!");
            return;
        }
        $id = $args[0];
        if(strlen($id) < 17 or (!is_numeric($id))) {
            $sender->sendMessage(TextFormat::RED . "We are looking for your numeric discord id, which is 17-18 digits. If you are unsure about your id, type the command \"?id\" in the #bot-commands channel.");
            return;
        }
        $manager->fetchDiscordUser($id, $sender, function(NexusPlayer $player) use($manager, $id): void {
            if(($user = $manager->getDiscordUser($id)) !== null) {
                $player->sendMessage(TextFormat::RED . "The Discord User: {$user->getTag()}, is already linked to {$user->getOwner()}!");
                return;
            }
            $player->sendMessage(TextFormat::YELLOW . "You have initiated the discord confirmation process. Please make sure you enable \"Allow direct messages from server members\" and privately message \"NexusPE\" the command ". TextFormat::AQUA .  "?confirm " . $player->getName());
            SocketRequests::sendConfirmationRequest($player, $id);
            $player->saveData();
        });

    }
}