<?php

namespace core\forms;

use core\NexusPlayer;
use core\trail\types\DoubleHelixTrail;
use core\trail\types\FireTrail;
use core\trail\types\HelixTrail;
use core\trail\types\RingTrail;
use libs\form\MenuForm;
use libs\form\MenuOption;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class TrailMenuForm extends MenuForm {

    /**
     * TrailMenuForm constructor.
     */
    public function __construct() {
        $options = [];
        $options[] = new MenuOption("None");
        $options[] = new MenuOption("Fire Trail");
        $options[] = new MenuOption("Helix Trail");
        $options[] = new MenuOption("Double Helix Trail");
        $options[] = new MenuOption("Ring Trail");
        parent::__construct(TextFormat::BOLD . TextFormat::AQUA . "Trail Selector", "Which trail would you like to use?", $options);
    }

    /**
     * @param Player $player
     * @param int $selectedOption
     */
    public function onSubmit(Player $player, int $selectedOption): void {
        if(!$player instanceof NexusPlayer) {
            return;
        }
        switch($selectedOption) {
            case 0:
                $player->setTrail();
                return;
                break;
            case 1:
                $player->setTrail(new FireTrail($player));
                return;
                break;
            case 2:
                $player->setTrail(new HelixTrail($player));
                return;
                break;
            case 3:
                $player->setTrail(new DoubleHelixTrail($player));
                return;
                break;
            case 4:
                $player->setTrail(new RingTrail($player));
                return;
                break;
        }
    }
}