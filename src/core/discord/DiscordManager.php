<?php

declare(strict_types = 1);

namespace core\discord;

use core\Nexus;
use core\NexusPlayer;

class DiscordManager {
    
    public const TOKEN = "";

    /** @var Nexus */
    private $core;

    /** @var ?DiscordUser[] */
    private $users = [];

    /**
     * DiscordManager constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
    }

    /**
     * @param string $id
     * @param NexusPlayer|null $player
     * @param callable|null $callable
     */
    public function fetchDiscordUser(string $id, ?NexusPlayer $player = null, ?callable $callable = null): void {
        if(isset($this->users[$id])) {
            if($player !== null and $callable !== null) {
                $callable($player);
            }
            return;
        }
        $connector = $this->core->getProvider()->getConnector();
        $connector->executeSelect("SELECT username FROM players WHERE discord = ?;", "s", [
            $id
        ], function(array $rows) use($id, $player, $callable) {
            foreach($rows as ["username" => $username]) {
                $this->addDiscordUser($id, $username);
            }
            if($player !== null and $callable !== null) {
                $callable($player);
            }
        });
    }

    /**
     * @param string $id
     *
     * @return DiscordUser|null
     */
    public function getDiscordUser(string $id): ?DiscordUser {
        if(!isset($this->users[$id])) {
            $this->fetchDiscordUser($id);
        }
        return $this->users[$id] ?? null;
    }

    /**
     * @param string $id
     * @param string $owner
     */
    public function addDiscordUser(string $id, string $owner): void {
        if(!isset($this->users[$id])) {
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "Authorization: Bot " . self::TOKEN
                ],
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false
                ]
            ];
            $context = stream_context_create($opts);
            $file = file_get_contents("https://discord.com/api/v8/users/$id", false, $context);
            if($file === false) {
                $this->users[$id] = null;
            }
            $data = json_decode($file, true);
            $this->users[$id] = new DiscordUser($data, $owner);
        }
    }

    /**
     * @param string $id
     */
    public function removeDiscordUser(string $id): void {
        if(isset($this->users[$id])){
            unset($this->users[$id]);
        }
    }
}
