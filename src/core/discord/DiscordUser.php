<?php

namespace core\discord;

class DiscordUser {

    /** @var string */
    private $id;

    /** @var string */
    private $username;

    /** @var int */
    private $discriminator;

    /** @var string */
    private $owner;

    /**
     * DiscordUser constructor.
     *
     * @param array $data
     * @param string $owner
     */
    public function __construct(array $data, string $owner) {
        $this->id = $data["id"];
        $this->username = $data["username"];
        $this->discriminator = $data["discriminator"];
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTag(): string {
        return $this->username . "#" . $this->discriminator;
    }

    /**
     * @return string
     */
    public function getOwner(): string {
        return $this->owner;
    }
}