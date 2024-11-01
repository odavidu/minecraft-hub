<?php

declare(strict_types = 1);

namespace core\server;

class Server {

    /** @var string */
    protected $name;

    /** @var string */
    protected $ip;

    /** @var int */
    protected $port;

    /** @var int */
    protected $players = 0;

    /** @var bool */
    protected $status = false;

    /**
     * Server constructor.
     *
     * @param string $name
     * @param string $ip
     * @param int $port
     */
    public function __construct(string $name, string $ip, int $port) {
        $this->name = $name;
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIP(): string {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort(): int {
        return $this->port;
    }

    /**
     * @return int
     */
    public function getPlayers(): int {
        return $this->players;
    }

    /**
     * @param int $players
     */
    public function setPlayers(int $players): void {
        $this->players = $players;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setOnline(bool $status = true): void {
        $this->status = $status;
    }
}