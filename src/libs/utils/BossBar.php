<?php

namespace libs\utils;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\player\Player;

class BossBar {

    /** @var Player */
    private $player;

    /** @var float */
    private $healthPercent = 100;

    /** @var string */
    private $title = "";

    /** @var int */
    private $uniqueId;

    /** @var bool */
    private $spawned = false;

    /**
     * BossBar constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player) {
        $this->player = $player;
    }

    /**
     * @return bool
     */
    public function isSpawned(): bool {
        return $this->spawned;
    }

    public function spawn() {
        if(!$this->spawned) {
            $this->uniqueId = Entity::nextRuntimeId();
            $pk = new AddActorPacket();
            $pk->type = EntityIds::ZOMBIE;
            $pk->entityRuntimeId = $this->uniqueId;
            $pk->position = new Vector3(0, 0, 0);
            $flags = (
                0 ^ 1 << EntityMetadataFlags::SILENT ^ 1 << EntityMetadataFlags::INVISIBLE ^ 1 << EntityMetadataFlags::NO_AI
            );
            $pk->metadata = [
                EntityMetadataProperties::LEAD_HOLDER_EID => new LongMetadataProperty(-1),
                EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
                EntityMetadataProperties::SCALE => new FloatMetadataProperty(0),
                EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->title),
                EntityMetadataProperties::BOUNDING_BOX_WIDTH => new FloatMetadataProperty(0),
                EntityMetadataProperties::BOUNDING_BOX_HEIGHT => new FloatMetadataProperty(0),
            ];
            $this->player->getNetworkSession()->sendDataPacket($pk);
            $pk = new BossEventPacket();
            $pk->bossEid = $this->uniqueId;
            $pk->eventType = BossEventPacket::TYPE_SHOW;
            $pk->unknownShort = 1;
            $pk->color = 0;
            $pk->overlay = 0;
            $pk->title = $this->title;
            $pk->healthPercent = $this->healthPercent / 100;
            $this->player->getNetworkSession()->sendDataPacket($pk);
            $this->spawned = true;
        }
    }

    /**
     * @param string $title
     * @param string $subtitle
     * @param float $healthPercent
     */
    public function update(string $title, float $healthPercent) {
        if(!$this->isSpawned()) {
            $this->spawn();
        }
        if($healthPercent > 100) {
            $healthPercent = 100;
        }
        $this->title = $title;
        $this->healthPercent = $healthPercent;
        $pk = new BossEventPacket();
        $pk->bossEid = $this->uniqueId;
        $pk->eventType = BossEventPacket::TYPE_TITLE;
        $pk->title = "$this->title";
        $this->player->getNetworkSession()->sendDataPacket($pk);
        $pk = new BossEventPacket();
        $pk->bossEid = $this->uniqueId;
        $pk->eventType = BossEventPacket::TYPE_HEALTH_PERCENT;
        $pk->healthPercent = $this->healthPercent / 100;
        $this->player->getNetworkSession()->sendDataPacket($pk);
        $this->spawn();
    }

    public function despawn() {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->uniqueId;
        $this->player->getNetworkSession()->sendDataPacket($pk);
        $this->spawned = false;
    }

    /**
     * @return int
     */
    public function getUniqueId(): int {
        return $this->uniqueId;
    }

    /**
     * @return float
     */
    public function getHealthPercent(): float {
        return $this->healthPercent;
    }
}