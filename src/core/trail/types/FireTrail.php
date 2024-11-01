<?php

namespace core\trail\types;

use core\trail\Trail;
use pocketmine\math\Vector3;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\SmokeParticle;

class FireTrail extends Trail {

    public function tick(): void {
        if(!$this->owner->isOnline()) {
            return;
        }
        if(!$this->owner->isLoaded()) {
            return;
        }
        $level = $this->owner->getWorld();
        if($level === null) {
            return;
        }
        $pos = $this->owner->getPosition();
        for($i = 0; $i <= 4; $i++) {
            $particle = new FlameParticle();
            $level->addParticle(new Vector3($pos->x + (mt_rand(0, 10) / 10), $pos->y, $pos->z + (mt_rand(0, 10) / 10)), $particle);
            $particle = new SmokeParticle();
            $level->addParticle(new Vector3($pos->x + (mt_rand(0, 10) / 10), $pos->y, $pos->z + (mt_rand(0, 10) / 10)), $particle);
        }
    }
}