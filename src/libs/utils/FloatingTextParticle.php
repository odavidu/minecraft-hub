<?php

declare(strict_types = 1);

namespace libs\utils;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class FloatingTextParticle extends \pocketmine\world\particle\FloatingTextParticle {

    /** @var string */
    private $identifier;

    /** @var string */
    private $message;

    /** @var World */
    private $level;

    /** @var Position */
    private $position;

    /**
     * FloatingTextParticle constructor.
     *
     * @param Position $pos
     * @param string $identifier
     * @param string $message
     */
    public function __construct(Position $pos, string $identifier, string $message) {
        parent::__construct("", "");
        $this->level = $pos->getWorld();
        $this->position = $pos;
        $this->identifier = $identifier;
        $this->message = $message;
        $this->update();
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @return World
     */
    public function getLevel(): World {
        return $this->level;
    }

    /**
     * @param null|string $message
     */
    public function update(?string $message = null): void {
        $this->message = $message ?? $this->message;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    public function sendChangesToAll(): void {
        foreach(Server::getInstance()->getOnlinePlayers() as $player) {
            $this->sendChangesTo($player);
        }
    }

    /**
     * @param Position $position
     */
    public function move(Position $position) {
        $this->position = $position;
    }

    /**
     * @param Player $player
     */
    public function sendChangesTo(Player $player): void {
        $this->setTitle($this->message);
        $level = $player->getWorld();
        if($level === null) {
            return;
        }
        if($this->level->getFolderName() !== $level->getFolderName()) {
            return;
        }
        $this->level->addParticle($this->position, $this, [$player]);
    }

    /**
     * @param Player $player
     */
    public function spawn(Player $player): void {
        $this->setInvisible(false);
        $level = $player->getWorld();
        if($level === null) {
            return;
        }
        $this->level->addParticle($this->position, $this, [$player]);
    }

    /**
     * @param Player $player
     */
    public function despawn(Player $player): void {
        $this->setInvisible(true);
        $level = $player->getWorld();
        if($level === null) {
            return;
        }
        $this->level->addParticle($this->position, $this, [$player]);
    }
}