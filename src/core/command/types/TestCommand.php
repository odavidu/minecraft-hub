<?php
declare(strict_types=1);

namespace core\command\types;

use core\command\utils\Command;
use core\NexusPlayer;
use libs\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class TestCommand extends Command {

    /**
     * TestCommand constructor.
     */
    public function __construct() {
        parent::__construct("test", "Has a mysterious function, only could be executed by IsThatSwifty.", "/test");
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
//        $opts = [
//            "http" => [
//                "method" => "GET",
//                "header" => "Authorization: Bot (deleted)"
//            ],
//            "ssl" => [
//                "verify_peer" => false,
//                "verify_peer_name" => false
//            ]
//        ];
//        $context = stream_context_create($opts);
//        $file = file_get_contents("https://discord.com/api/v8/users/341685934133346305", false, $context);
//        var_dump($file);
    }
}
