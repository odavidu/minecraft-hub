<?php

declare(strict_types = 1);

namespace core\npc;

use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class NPC {

    /** @var string */
    protected $nameTag;

    /** @var Skin */
    protected $skin;

    /** @var int */
    protected $entityId;

    /** @var Uuid */
    protected $uuid;

    /** @var Position */
    protected $position;

    /** @var bool[] */
    protected $spawned = [];

    /**
     * NPC constructor.
     *
     * @param Skin $skin
     * @param Position $position
     * @param string $nameTag
     */
    public function __construct(Skin $skin, Position $position, string $nameTag) {
        $this->skin = $skin;
        $this->position = $position;
        $this->nameTag = $nameTag;
        $this->entityId = Entity::nextRuntimeId();
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player): void {
        $this->spawned[$player->getUniqueId()->getBytes()] = true;
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->entityId, $this->getNameTag(), SkinAdapterSingleton::get()->toSkinData($this->skin))];
        $player->getNetworkSession()->sendDataPacket($pk);
        $xdiff = $player->getPosition()->x - $this->position->x;
        $zdiff = $player->getPosition()->z - $this->position->z;
        $angle = atan2($zdiff, $xdiff);
        $yaw = (($angle * 180) / M_PI) - 90;
        $ydiff = $player->getPosition()->y - $this->position->y;
        $v = new Vector2($this->position->x, $this->position->z);
        $dist = $v->distance($player->getPosition()->x, $player->getPosition()->z);
        $angle = atan2($dist, $ydiff);
        $pitch = (($angle * 180) / M_PI) - 90;
        $pk = new AddPlayerPacket();
        $pk->uuid = $this->getUniqueId();
        $pk->username = $this->nameTag;
        $pk->entityRuntimeId = $this->entityId;
        $pk->position = $this->position->asVector3();
        $pk->yaw = $yaw;
        $pk->pitch = $pitch;
        /** @var TypeConverter $converter */
        $converter = TypeConverter::getInstance();
        /** @var ItemFactory $itemFactory */
        $itemFactory = ItemFactory::getInstance();
        $pk->item = ItemStackWrapper::legacy($converter->coreItemStackToNet($itemFactory->get(ItemIds::AIR)));
        $flags = (
            1 << EntityMetadataFlags::ALWAYS_SHOW_NAMETAG
            ^ 1 << EntityMetadataFlags::CAN_SHOW_NAMETAG
        );
        $pk->metadata = [
            EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
            EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->nameTag),
            EntityMetadataProperties::LEAD_HOLDER_EID => new LongMetadataProperty(-1)
        ];
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->setNameTag($player);
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        $pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @return string
     */
    public function getNameTag(): string {
        return $this->nameTag;
    }

    /**
     * @param Player $player
     */
    public function setNameTag(Player $player): void {
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = $this->entityId;
        $pk->metadata = [
            EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->nameTag)
        ];
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @return UuidInterface
     */
    public function getUniqueId(): UuidInterface {
        return $this->uuid;
    }

    /**
     * @param Player $player
     */
    public function despawnFrom(Player $player): void {
        unset($this->spawned[$player->getUniqueId()->getBytes()]);
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->entityId;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function isSpawned(Player $player): bool {
        return isset($this->spawned[$player->getUniqueId()->getBytes()]);
    }

    /**
     * @param Player $player
     */
    public function move(Player $player): void {
        $xdiff = $player->getPosition()->x - $this->position->x;
        $zdiff = $player->getPosition()->z - $this->position->z;
        $angle = atan2($zdiff, $xdiff);
        $yaw = (($angle * 180) / M_PI) - 90;
        $ydiff = $player->getPosition()->y - $this->position->y;
        $v = new Vector2($this->position->x, $this->position->z);
        $dist = $v->distance($player->getPosition()->x, $player->getPosition()->z);
        $angle = atan2($dist, $ydiff);
        $pitch = (($angle * 180) / M_PI) - 90;
        $pk = new MovePlayerPacket();
        $pk->entityRuntimeId = $this->entityId;
        $pk->position = $this->position->asVector3()->add(0, 1.62, 0);
        $pk->yaw = $yaw;
        $pk->pitch = $pitch;
        $pk->headYaw = $yaw;
        $pk->onGround = true;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @return Skin
     */
    public function getSkin(): Skin {
        return $this->skin;
    }

    /**
     * @return int
     */
    public function getEntityId(): int {
        return $this->entityId;
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function hasSpawnedTo(Player $player): bool {
        return isset($this->spawned[$player->getUniqueId()->getBytes()]) ? $this->spawned[$player->getUniqueId()->getBytes()] : false;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @param Player $player
     */
    abstract public function tick(Player $player): void;

    /**
     * @return string
     */
    abstract public function updateNameTag(): string ;

    /**
     * @param Player $player
     */
    abstract public function tap(Player $player): void;
}