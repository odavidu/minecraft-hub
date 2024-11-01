<?php

namespace core;

use core\provider\event\PlayerLoadEvent;
use core\provider\SocketRequests;
use core\provider\task\LoadScreenTask;
use core\trail\Trail;
use libs\utils\BossBar;
use libs\utils\Scoreboard;
use libs\utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class NexusPlayer extends Player {

    /** @var string|null */
    private $discordId = null;

    /** @var int */
    private $registerDate;

    /** @var int */
    private $successCount = 0;

    /** @var bool */
    private $loaded = false;

    /** @var Nexus */
    private $core;

    /** @var Scoreboard */
    private $scoreboard;

    /** @var BossBar */
    private $bossBar;

    /** @var null|Trail */
    private $trail = null;

    /** @var string */
    private $connected = "hub.nexuspe.net";

    /** @var int */
    private $importance = 0;

    /**
     * @return string
     */
    public function getConnected(): string {
        return $this->connected;
    }

    /**
     * @param string $connected
     */
    public function setConnected(string $connected): void {
        $this->connected = $connected;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool {
        return $this->loaded;
    }

    public function setLoaded(): void {
        $this->loaded = true;
    }

    public function testLoad(): void {
        if($this->successCount === 1) {
            $this->setLoaded();
            $event = new PlayerLoadEvent($this);
            $event->call();
        }
    }

    public function load(): void {
        switch($this->connected) {
            case "fac.nexuspe.net":
                Nexus::getInstance()->getServerManager()->addToQueue($this, "hub.nexuspe.net", 19134);
                break;
            case "pvp.nexuspe.net":
                Nexus::getInstance()->getServerManager()->addToQueue($this, "hub.nexuspe.net", 19136);
                break;
            case "psn.nexuspe.net":
                Nexus::getInstance()->getServerManager()->addToQueue($this, "hub.nexuspe.net", 19100);
                break;
            case "test.nexuspe.net":
                Nexus::getInstance()->getServerManager()->addToQueue($this, "hub.nexuspe.net", 19123);
                break;
            default:
                break;
        }
        $this->core = Nexus::getInstance();
        $this->scoreboard = new Scoreboard($this);
        $this->bossBar = new BossBar($this);
        Nexus::getInstance()->getScheduler()->scheduleRepeatingTask(new LoadScreenTask($this), 20);
        $uuid = $this->getUniqueId()->getBytes();
        $connector = $this->core->getProvider()->getConnector();
        $connector->executeSelect("SELECT registerDate, discord, skinData FROM players WHERE uuid = ?;", "s", [
            $uuid
        ], function(array $rows) {
            if(empty($rows)) {
                $this->registerDate = time();
                $img = Utils::createImageFromSkinData($this->getSkin()->getSkinData());
                $head = Utils::getHeadSkinFromImage($img);
                imagepng($head, DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . $this->getName() . ".png");
            }
            else {
                foreach($rows as [
                    "registerDate" => $registerDate,
                    "discord" => $discordId,
                    "skinData" => $skinData
                ]) {
                    if($registerDate === null) {
                        $this->registerDate = time();
                    }
                    else {
                        $this->registerDate = $registerDate;
                    }
                    if($this->getSkin()->getSkinData() !== $skinData) {
                        $img = Utils::createImageFromSkinData($this->getSkin()->getSkinData());
                        $head = Utils::getHeadSkinFromImage($img);
                        imagepng($head, DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "skins" . DIRECTORY_SEPARATOR . $this->getName() . ".png");
                    }
                    $this->setDiscordId($discordId);
                }
            }
            if($this->discordId === null) {
                $this->sendMessage(TextFormat::RED . "Your discord doesn't seem to be linked yet! You may link by doing /linkdiscord!");
            }
            $this->successCount++;
        });
    }

    public function saveData(): void {
        $uuid = $this->getUniqueId()->getBytes();
        $skinData = $this->getSkin()->getSkinData();
        $username = $this->getName();
        $stmt = $this->core->getProvider()->getDatabase()->prepare("REPLACE INTO players(uuid, username, registerDate, discord, skinData) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $uuid, $username, $this->registerDate, $this->discordId, $skinData);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @param string|null $discordId
     */
    public function setDiscordId(?string $discordId): void {
        if($discordId !== null) {
            Nexus::getInstance()->getDiscordManager()->addDiscordUser($discordId, $this->getName());
        }
        if($this->discordId !== null and $discordId === null) {
            SocketRequests::sendResetRoleRequest($this, $this->discordId);
        }
        $this->discordId = $discordId;
        $uuid = $this->getUniqueId()->getBytes();
        if($this->discordId !== null) {
            $connector = $this->core->getFactionProvider()->getConnector();
            $connector->executeSelect("SELECT rankId FROM players WHERE uuid = ?", "s", [
                $uuid
            ], function(array $rows) {
                foreach($rows as ["rankId" => $rankId]) {
                    if($rankId >= 6 and $rankId <= 8) {
                        $role = "「⚔」Tier " . Utils::getRomanNumber($rankId - 5);
                    }
                    if(isset($role)) {
                        SocketRequests::sendSetRoleRequest($this, $this->discordId, $role);
                    }
                    if($rankId >= 6) {
                        $this->importance += $rankId - 5;
                    }
                }
            });
            $connector = $this->core->getPrisonProvider()->getConnector();
            $connector->executeSelect("SELECT rankId FROM players WHERE uuid = ?", "s", [
                $uuid
            ], function(array $rows) {
                foreach($rows as ["rankId" => $rankId]) {
                    if($rankId >= 1 and $rankId <= 7) {
                        $role = "「⛏」Tier " . Utils::getRomanNumber($rankId);
                    }
                    if(isset($role)) {
                        SocketRequests::sendSetRoleRequest($this, $this->discordId, $role);
                    }
                    $this->importance += $rankId;
                }
            });
        }
    }

    /**
     * @return string
     */
    public function getDiscordId(): ?string {
        return $this->discordId;
    }

    /**
     * @return int
     */
    public function getRegisterDate(): int {
        return $this->registerDate;
    }

    /**
     * @return Scoreboard
     */
    public function getScoreboard(): Scoreboard {
        return $this->scoreboard;
    }

    /**
     * @return BossBar
     */
    public function getBossBar(): BossBar {
        return $this->bossBar;
    }

    /**
     * @return Nexus
     */
    public function getCore(): Nexus {
        return $this->core;
    }

    /**
     * @return Trail|null
     */
    public function getTrail(): ?Trail {
        return $this->trail;
    }

    /**
     * @param Trail|null $trail
     */
    public function setTrail(?Trail $trail = null): void {
        $this->trail = $trail;
    }

    /**
     * @return int
     */
    public function getImportance(): int {
        return $this->importance;
    }
}