<?php

namespace core;

use core\command\CommandManager;
use core\discord\DiscordManager;
use core\npc\NPCManager;
use core\provider\MySQLProvider;
use core\restart\RestartManager;
use core\server\ServerManager;
use core\trail\task\TrailHeartbeartTask;
use core\update\UpdateManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Nexus extends PluginBase {

    /** @var NPCManager */
    private $npcManager;

    /** @var UpdateManager */
    private $updateManager;

    /** @var CommandManager */
    private $commandManager;

    /** @var RestartManager */
    private $restartManager;

    /** @var ServerManager */
    private $serverManager;

    /** @var DiscordManager */
    private $discordManager;

    /** @var MySQLProvider */
    private $provider;

    /** @var MySQLProvider */
    private $prisonProvider;

    /** @var MySQLProvider */
    private $factionProvider;

    /** @var self */
    private static $instance;

    /**
     * @return Nexus
     */
    public static function getInstance(): Nexus {
        return self::$instance;
    }

    public function onLoad(): void {
        self::$instance = $this;
    }

    public function onEnable(): void {
        foreach($this->getServer()->getWorldManager()->getWorlds() as $level) {
            $level->setTime(0);
            $level->stopTime();
        }
        $this->serverManager = new ServerManager($this);
        $this->npcManager = new NPCManager($this);
        $this->updateManager = new UpdateManager($this);
        $this->commandManager = new CommandManager($this);
        $this->restartManager = new RestartManager($this);
        $this->discordManager = new DiscordManager($this);
        $this->provider = new MySQLProvider($this, "Hub");
        $this->prisonProvider = new MySQLProvider($this, "Prison");
        $this->factionProvider = new MySQLProvider($this, "OPFaction");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new TrailHeartbeartTask($this), 5);
		$this->getServer()->getNetwork()->setName(TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Nexus " . TextFormat::DARK_AQUA . "PE");
	}

    /**
     * @return NPCManager
     */
	public function getNPCManager(): NPCManager {
        return $this->npcManager;
    }

    /**
     * @return UpdateManager
     */
    public function getUpdateManager(): UpdateManager {
        return $this->updateManager;
    }

    /**
     * @return CommandManager
     */
    public function getCommandManager(): CommandManager {
        return $this->commandManager;
    }

    /**
     * @return RestartManager
     */
    public function getRestartManager(): RestartManager {
        return $this->restartManager;
    }

    /**
     * @return ServerManager
     */
    public function getServerManager(): ServerManager {
        return $this->serverManager;
    }

    /**
     * @return DiscordManager
     */
    public function getDiscordManager(): DiscordManager {
        return $this->discordManager;
    }

    /**
     * @return MySQLProvider
     */
    public function getProvider(): MySQLProvider {
        return $this->provider;
    }

    /**
     * @return MySQLProvider
     */
    public function getPrisonProvider(): MySQLProvider {
        return $this->prisonProvider;
    }

    /**
     * @return MySQLProvider
     */
    public function getFactionProvider(): MySQLProvider {
        return $this->factionProvider;
    }
}
