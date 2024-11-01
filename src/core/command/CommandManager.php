<?php

declare(strict_types = 1);

namespace core\command;

use core\command\types\LinkDiscordCommand;
use core\command\types\SaveSkinCommand;
use core\command\types\TestCommand;
use core\command\types\UnlinkDiscordCommand;
use core\Nexus;
use pocketmine\command\Command;
use pocketmine\plugin\PluginException;

class CommandManager {

    /** @var Nexus */
    private $core;

    /**
     * CommandManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $this->registerCommand(new LinkDiscordCommand());
        $this->registerCommand(new SaveSkinCommand());
        $this->registerCommand(new TestCommand());
        $this->registerCommand(new UnlinkDiscordCommand());
        $this->unregisterCommand("about");
        $this->unregisterCommand("me");
        $this->unregisterCommand("particle");
        $this->unregisterCommand("title");
    }

    /**
     * @param Command $command
     */
    public function registerCommand(Command $command): void {
        $commandMap = $this->core->getServer()->getCommandMap();
        $existingCommand = $commandMap->getCommand($command->getName());
        if($existingCommand !== null) {
            $commandMap->unregister($existingCommand);
        }
        $commandMap->register($command->getName(), $command);
    }

    /**
     * @param string $name
     */
    public function unregisterCommand(string $name): void {
        $commandMap = $this->core->getServer()->getCommandMap();
        $command = $commandMap->getCommand($name);
        if($command === null) {
            throw new PluginException("Invalid command: $name to un-register.");
        }
        $commandMap->unregister($commandMap->getCommand($name));
    }
}