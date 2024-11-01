<?php

declare(strict_types = 1);

namespace core\update\task;

use core\Nexus;
use core\update\UpdateManager;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\FloatingTextParticle;

class UpdateHeartbeatTask extends Task {

    /** @var UpdateManager */
    private $manager;

    /** @var FloatingTextParticle */
    private $floatingText;

    /**
     * UpdateHeartbeatTask constructor.
     *
     * @param UpdateManager $manager
     */
    public function __construct(UpdateManager $manager) {
        $this->manager = $manager;
    }

    public function onRun(): void {
        if(Server::getInstance()->getWorldManager()->getDefaultWorld() === null) {
            return;
        }
        if(!isset($this->floatingText)) {
            $this->floatingText = new FloatingTextParticle("", "");
            Server::getInstance()->getWorldManager()->getDefaultWorld()->addParticle(new Vector3(-10.5, 51, 0.5), $this->floatingText);
        }
        $count = 0;
        foreach(Nexus::getInstance()->getServerManager()->getServers() as $server) {
            if($server->isOnline()) {
                $count += $server->getPlayers();
            }
        }
        $count += count(Server::getInstance()->getOnlinePlayers());
        $this->floatingText->setText("§b§Nexus§3PE §r§7Lobby\n \n§fThere are currently §l§b" . $count . " §rgamers playing on our official servers.\n \n§c§lSHOP: §r§fstore.nexuspe.net\n§9§lDISCORD: §r§fdiscord.gg/nexuspe\n§d§lVOTE: §r§fvote.nexuspe.net");
        Server::getInstance()->getWorldManager()->getDefaultWorld()->addParticle(new Vector3(-10.5, 51, 0.5), $this->floatingText);
    }
}
