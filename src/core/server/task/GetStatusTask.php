<?php

declare(strict_types = 1);

namespace core\server\task;

use core\Nexus;
use libs\utils\PMQuery;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class GetStatusTask extends AsyncTask {

    /** @var string */
    private $ip;

    /** @var int */
    private $port;

    /**
     * GetStatusTask constructor.
     *
     * @param string $ip
     * @param int $port
     */
    public function __construct(string $ip, int $port) {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function onRun(): void {
        $info = PMQuery::query($this->ip, $this->port);
        $this->setResult($info);
    }

    public function onCompletion(): void {
        $server = Nexus::getInstance()->getServerManager()->getServer($this->ip, $this->port);
        if($server === null) {
            return;
        }
        $result = $this->getResult();
        if(empty($result)) {
            $server->setOnline(false);
            $server->setPlayers(0);
            return;
        }
        $server->setOnline();
        $server->setPlayers((int)$result['num']);
    }
}