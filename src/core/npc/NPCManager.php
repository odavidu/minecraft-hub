<?php

declare(strict_types = 1);

namespace core\npc;

use core\Nexus;
use core\npc\task\NPCHeartbeatTask;
use core\npc\types\Events;
use core\npc\types\OPFaction;
use core\npc\types\Prison;

class NPCManager {

    /** @var Nexus */
    private $core;

    /** @var NPC[] */
    private $npcs = [];

    /**
     * NPCManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getServer()->getPluginManager()->registerEvents(new NPCListener($core), $core);
        $core->getScheduler()->scheduleRepeatingTask(new NPCHeartbeatTask($this), 100);
        $this->init();
    }

    public function init() {
        $this->addNPC(new OPFaction());
        $this->addNPC(new Prison());
        $this->addNPC(new Events());
    }

    /**
     * @return NPC[]
     */
    public function getNPCs(): array {
        return $this->npcs;
    }

    /**
     * @param int $entityId
     *
     * @return NPC|null
     */
    public function getNPC(int $entityId): ?NPC {
        return $this->npcs[$entityId] ?? null;
    }

    /**
     * @param NPC $npc
     */
    public function addNPC(NPC $npc): void {
        $this->npcs[$npc->getEntityId()] = $npc;
    }

    /**
     * @param NPC $npc
     */
    public function removeNPC(NPC $npc): void {
        unset($this->npcs[$npc->getEntityId()]);
    }
}