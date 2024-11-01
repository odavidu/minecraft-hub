<?php

declare(strict_types = 1);

namespace core\update\task;

use core\Nexus;
use core\NexusPlayer;
use core\provider\thread\MySQLThread;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CheckDiscordIDTask extends Task {

    /** @var NexusPlayer[] */
    private $players = [];

    public function onRun(): void {
        if(empty($this->players)) {
            $this->players = Server::getInstance()->getOnlinePlayers();
            return;
        }
        $player = array_shift($this->players);
        if($player->isOnline() and $player->isLoaded()) {
            $uuid = $player->getUniqueId()->getBytes();
            if($player->getDiscordId() === null) {
                $connector = Nexus::getInstance()->getProvider()->getConnector();
                $connector->executeSelect("SELECT discord FROM players WHERE uuid = ?;", "s", [
                    $uuid
                ], function(array $rows) use($player) {
                    foreach($rows as [
                        "discord" => $discordId
                    ]) {
                        if($discordId !== null) {
                            $player->setDiscordId($discordId);
                            $user = Nexus::getInstance()->getDiscordManager()->getDiscordUser($discordId);
                            $player->sendMessage(TextFormat::YELLOW . "Your Minecraft account has been linked to the Discord User: " . TextFormat::WHITE . $user->getTag());
                        }
                    }
                });
            }
        }
    }
}
