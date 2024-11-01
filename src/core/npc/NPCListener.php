<?php

declare(strict_types = 1);

namespace core\npc;

use core\Nexus;
use core\NexusPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

class NPCListener implements Listener {

    /** @var Nexus */
    private $core;

    /**
     * NPCListener constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @priority NORMAL
     *
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        foreach($this->core->getNPCManager()->getNPCs() as $npc) {
            $npc->spawnTo($player);
        }
    }

    /**
     * @priority NORMAL
     *
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event) {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if(!$player->isLoaded()) {
            return;
        }
        foreach($this->core->getNPCManager()->getNPCs() as $npc) {
            if($npc->getPosition()->getWorld() === null or $player->getWorld() === null) {
                continue;
            }
            if($npc->getPosition()->getWorld()->getFolderName() === $player->getWorld()->getFolderName()) {
                if($npc->getPosition()->distance($player->getPosition()) <= 20) {
                    $npc->move($player);
                }
            }
        }
    }

    /**
     * @priority NORMAL
     *
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $pk = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if($player === null) {
            return;
        }
        if($pk instanceof InventoryTransactionPacket and $pk->trData instanceof UseItemOnEntityTransactionData) {
            $npc = $this->core->getNPCManager()->getNPC($pk->trData->getEntityRuntimeId());
            if($npc === null) {
                return;
            }
            $npc->tap($player);
        }
    }
}