<?php

namespace core\trail\types;

use core\trail\Trail;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\world\particle\DustParticle;

class DoubleHelixTrail extends Trail {

    /** @var int */
    private $i = -1;

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
        $radio = 1;
        $pos = $this->owner->getPosition();
        for($i = 0; $i <= 1; $i++) {
            $this->i += 0.2;
            if($this->i > 2) {
                $this->i = -1;
            }
            $x = $radio * sin($this->i);
            $z = $radio * cos($this->i);
            $particle = new DustParticle(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            $level->addParticle(new Vector3($pos->x + $x, $pos->y + $this->i, $pos->z + $z), $particle);
            $x = -$radio * sin($this->i);
            $z = -$radio * cos($this->i);
            $particle = new DustParticle(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
            $level->addParticle(new Vector3($pos->x + $x, $pos->y + $this->i, $pos->z + $z), $particle);
        }
    }
}