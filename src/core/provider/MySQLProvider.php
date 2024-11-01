<?php

declare(strict_types = 1);

namespace core\provider;

use core\Nexus;
use core\provider\task\ReadResultsTask;
use core\provider\thread\MySQLThread;
use mysqli;

class MySQLProvider {
    
    public const IP = "127.0.0.1";
    
    public const USERNAME = "david";
    
    public const PASSWORD = "password";

    /** @var Nexus */
    private $core;

    /** @var mysqli */
    private $database;

    /** @var MySQLCredentials */
    private $credentials;

    /** @var string */
    private $schema;

    /** @var MySQLThread */
    private $thread;

    /**
     * MySQLProvider constructor.
     *
     * @param Nexus $core
     * @param string $schema
     */
    public function __construct(Nexus $core, string $schema) {
        $this->core = $core;
        $this->schema = $schema;
        $this->database = new mysqli(self::IP, self::USERNAME, self::PASSWORD, $this->schema);
        $this->credentials = new MySQLCredentials(self::IP, self::USERNAME, self::PASSWORD, $this->schema);
        $this->init();
        $this->thread = new MySQLThread($this->credentials);
        $this->thread->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
        $core->getScheduler()->scheduleRepeatingTask(new ReadResultsTask($this->thread), 1);
    }

    public function init(): void {
        $this->getDatabase()->query("CREATE TABLE IF NOT EXISTS players(
            uuid VARCHAR(36) PRIMARY KEY, 
            username VARCHAR(16), 
            registerDate BIGINT, 
            discord VARCHAR(18) DEFAULT NULL,
            skinData MEDIUMTEXT
        );");
    }

    /**
     * @return MySQLThread
     */
    public function getConnector(): MySQLThread {
        return $this->thread;
    }

    /**
     * @return MySQLThread
     */
    public function createNewThread(): MySQLThread {
        if(!$this->thread->isRunning()) {
            $this->thread = new MySQLThread($this->credentials);
            $this->thread->start(PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS);
        }
        return $this->thread;
    }

    /**
     * @return MySQLCredentials
     */
    public function getCredentials(): MySQLCredentials {
        return $this->credentials;
    }

    /**
     * @return mysqli
     */
    public function getDatabase(): mysqli {
        return $this->database;
    }
}
