<?php

namespace core\trail\types;

use core\trail\Trail;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\world\particle\DustParticle;

class RingTrail extends Trail {

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
        for($i = 0; $i <= 5; $i++) {
            $x = sin($i);
            $z = cos($i);
            $particle = new DustParticle(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            $level->addParticle(new Vector3($pos->x + $x, $pos->y, $pos->z + $z), $particle);
        }
    }
}