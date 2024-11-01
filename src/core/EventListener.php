<?php

namespace core;

use core\forms\TrailMenuForm;
use core\forms\TransferForm;
use core\provider\event\PlayerLoadEvent;
use core\update\task\ResetPlayersTask;
use libs\utils\PMQuery;
use libs\utils\Utils;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerTransferEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\permission\DefaultPermissions;
use pocketmine\utils\TextFormat;

class EventListener implements Listener {

    /** @var Nexus */
    private $core;

    /** @var bool[] */
    public $players = [];

    /**
     * EventListener constructor.
     *
     * @param Nexus $core
     */
    public function __construct(Nexus $core) {
        $this->core = $core;
        $core->getScheduler()->scheduleRepeatingTask(new ResetPlayersTask($this), 6000);
    }

    /**
     * @priority NORMAL
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        $player->teleport($this->core->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
    }

    /**
     * @priority NORMAL
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event) {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        $event->setJoinMessage("");
        $player->load();
    }

    /**
     * @param PlayerLoadEvent $event
     */
    public function onPlayerLoad(PlayerLoadEvent $event): void {
        $player = $event->getPlayer();
        $player->sendMessage(" ");
        $player->sendMessage(Utils::centerAlignText(TextFormat::WHITE . "Welcome " . TextFormat::GOLD . TextFormat::BOLD . $player->getName() . TextFormat::RESET . ", to " . TextFormat::RESET . TextFormat::BOLD . TextFormat::AQUA . "Nexus" . TextFormat::DARK_AQUA . "PE", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::GRAY . TextFormat::ITALIC . "Gateway to other game-modes, Minecraft Lobby", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::RED . "SHOP: " . TextFormat::RESET . TextFormat::WHITE . "store.nexuspe.net", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::BLUE . "DISCORD: " . TextFormat::RESET . TextFormat::WHITE . "discord.gg/nexuspe", 58));
        $player->sendMessage(Utils::centerAlignText(TextFormat::BOLD . TextFormat::LIGHT_PURPLE . "RECOMMENDED VERSION: " . TextFormat::RESET . TextFormat::WHITE . ProtocolInfo::MINECRAFT_VERSION, 58));
        $player->sendMessage(" ");
        if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $player->setNameTag(TextFormat::BOLD . TextFormat::DARK_RED . "<" . TextFormat::RED . "MVP" . str_repeat("+", $player->getImportance()) . TextFormat::DARK_RED . "> " . TextFormat::RESET . TextFormat::RED . $player->getName());
        }
        else {
            $player->setNameTag(TextFormat::BOLD . TextFormat::DARK_AQUA . "<" . TextFormat::AQUA . "VIP" . str_repeat("+", $player->getImportance()) . TextFormat::DARK_AQUA . "> " . TextFormat::RESET . TextFormat::GOLD . $player->getName());
        }
        $player->setNameTagAlwaysVisible(true);
        $selector = VanillaItems::COMPASS();
        $selector->setCustomName(TextFormat::RESET . TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "Server Selector");
        $selector->setLore(
            [
                "",
                TextFormat::RESET . TextFormat::WHITE . "Tap anywhere to open up transfer menu."
            ]
        );
        $player->getInventory()->setItem(4, $selector);
        $selector = VanillaItems::GRAY_DYE();
        $selector->setCustomName(TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . "Hide Players");
        $selector->setLore(
            [
                "",
                TextFormat::RESET . TextFormat::WHITE . "Tap anywhere hide players."
            ]
        );
        $player->getInventory()->setItem(0, $selector);
        $selector = ItemFactory::getInstance()->get(ItemIds::FIREWORKS, 0, 1);
        $selector->setCustomName(TextFormat::RESET . TextFormat::AQUA . TextFormat::BOLD . "Trail Selector");
        $selector->setLore(
            [
                "",
                TextFormat::RESET . TextFormat::WHITE . "Tap anywhere select a trail."
            ]
        );
        $player->getInventory()->setItem(8, $selector);
        $player->setAllowFlight(true);
        $player->getEffects()->clear();
        if(!isset($this->players[$player->getUniqueId()->getBytes()])) {
            $this->players[$player->getUniqueId()->getBytes()] = false;
        }
        foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if($this->players[$player->getUniqueId()->getBytes()] === true) {
                if($onlinePlayer->getUniqueId()->getBytes() === $player->getUniqueId()->getBytes()) {
                    continue;
                }
                $player->hidePlayer($onlinePlayer);
            }
        }
        foreach($this->players as $id => $value) {
            $onlinePlayer = $player->getServer()->getPlayerByRawUUID($id);
            if($onlinePlayer === null) {
                continue;
            }
            if($value === false) {
                continue;
            }
            if($onlinePlayer->getUniqueId()->getBytes() === $player->getUniqueId()->getBytes()) {
                continue;
            }
            $onlinePlayer->hidePlayer($player);
        }
    }

    /**
     * @priority NORMAL
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event) {
        $event->setQuitMessage("");
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if($player->isLoaded()) {
            $player->saveData();
        }
        $this->core->getServerManager()->removeFromQueue($player);
    }

    /**
     * @priority NORMAL
     * @param PlayerTransferEvent $event
     */
    public function onPlayerTransfer(PlayerTransferEvent $event) {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        $this->core->getServerManager()->removeFromQueue($player);
    }

    /**
     * @priority NORMAL
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event) {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        if(!$player->isLoaded()) {
            $event->cancel();
            return;
        }
        if($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $event->setFormat(TextFormat::BOLD . TextFormat::DARK_RED . "<" . TextFormat::RED . "MVP" . str_repeat("+", $player->getImportance()) . TextFormat::DARK_RED . "> " . TextFormat::RESET . TextFormat::RED . $player->getName()  . ": " . $event->getMessage());
            return;
        }
        $event->setFormat(TextFormat::BOLD . TextFormat::DARK_AQUA . "<" . TextFormat::AQUA . "VIP" . str_repeat("+", $player->getImportance()) . TextFormat::DARK_AQUA . "> " . TextFormat::RESET . TextFormat::GOLD . $player->getName() . TextFormat::YELLOW  . ": " . $event->getMessage());
    }

    /**
     * @priority NORMAL
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {
        /** @var NexusPlayer $player */
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->getId() === ItemIds::COMPASS) {
            $player->sendForm(new TransferForm($player));
        }
        if($item->getId() === ItemIds::FIREWORKS) {
            $player->sendForm(new TrailMenuForm());
        }
        if($item instanceof Dye and $item->getColor()->equals(VanillaItems::GRAY_DYE()->getColor())) {
            $selector = VanillaItems::LIME_DYE();
            $selector->setCustomName(TextFormat::RESET . TextFormat::GREEN . TextFormat::BOLD . "Show Players");
            $selector->setLore([
                "",
                TextFormat::RESET . TextFormat::WHITE . "Tap anywhere show players."
            ]);
            $player->getInventory()->setItem(0, $selector);
            foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer) {
                if($onlinePlayer->getUniqueId()->getBytes() === $player->getUniqueId()->getBytes()) {
                    continue;
                }
                $player->hidePlayer($onlinePlayer);
            }
            $this->players[$player->getUniqueId()->getBytes()] = true;
        }
        if($item instanceof Dye and $item->getColor()->equals(VanillaItems::LIME_DYE()->getColor())) {
            $selector = VanillaItems::GRAY_DYE();
            $selector->setCustomName(TextFormat::RESET . TextFormat::GRAY . TextFormat::BOLD . "Hide Players");
            $selector->setLore([
                "",
                TextFormat::RESET . TextFormat::WHITE . "Tap anywhere hide players."
            ]);
            $player->getInventory()->setItem(0, $selector);
            foreach($player->getServer()->getOnlinePlayers() as $onlinePlayer) {
                if($onlinePlayer->getUniqueId()->getBytes() === $player->getUniqueId()->getBytes()) {
                    continue;
                }
                $player->showPlayer($onlinePlayer);
            }
            $this->players[$player->getUniqueId()->getBytes()] = false;
        }
    }

    /**
     * @priority NORMAL
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof NexusPlayer) {
            return;
        }
        if(!$player->isLoaded()) {
            $event->cancel();
            return;
        }
        $to = $event->getTo();
        if($to->getY() < 0) {
            $level = $to->getWorld();
            if($level === null) {
                return;
            }
            $player->teleport($level->getSpawnLocation());
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerExhaustEvent $event
     */
    public function onPlayerExhaust(PlayerExhaustEvent $event) {
        $event->cancel();
    }

    /**
     * @priority LOWEST
     * @param PlayerDropItemEvent $event
     */
    public function onPlayerDropItem(PlayerDropItemEvent $event) {
        $event->cancel();
    }

    /**
     * @param PlayerCreationEvent $event
     */
    public function onPlayerCreation(PlayerCreationEvent $event): void {
        $event->setPlayerClass(NexusPlayer::class);
    }

    /**
     * @priority LOWEST
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();
        if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $event->cancel();
        }
    }

    /**
     * @priority LOWEST
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        if(!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $event->cancel();
        }
    }

    /**
     * @priority LOWEST
     * @param InventoryTransactionEvent $event
     */
    public function onInventoryTransaction(InventoryTransactionEvent $event) {
        foreach($event->getTransaction()->getActions() as $action) {
            if($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                if($inventory instanceof PlayerInventory or $inventory instanceof PlayerCursorInventory) {
                    if($inventory->getHolder()->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        continue;
                    }
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @priority LOWEST
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event) {
        $event->cancel();
    }

    /**
     * @priority NORMAL
     * @param QueryRegenerateEvent $event
     */
    public function onQueryRegenerate(QueryRegenerateEvent $event) {
        $count = 0;
        foreach($this->core->getServerManager()->getServers() as $server) {
            if($server->isOnline()) {
                $count += $server->getPlayers();
            }
        }
        $count += count($this->core->getServer()->getOnlinePlayers());
        $event->getQueryInfo()->setPlayerCount($count);
        $event->getQueryInfo()->setMaxPlayerCount($count + 1);
    }

    /**
     * @param DataPacketSendEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $pk = $event->getPacket();
        /** @var NexusPlayer $player */
        $player = $event->getOrigin()->getPlayer();
        if($player === null) {
            return;
        }
        if($pk instanceof LoginPacket) {
            $ip = explode(":", $pk->serverAddress)[0];
            $player->setConnected($ip);
        }
    }
}