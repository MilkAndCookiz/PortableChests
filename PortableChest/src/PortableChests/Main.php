<?php

namespace PortableChests;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Chest;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ListTag;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;

class Main extends PluginBase implements Listener {

    private array $messages;
    private string $chestNameColor;
    private string $chestName;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->messages = $this->getConfig()->get("messages", [
            "chest_made_portable" => "&aChest has been made portable!",
            "chest_placed" => "&aPortable chest has been placed!",
            "not_authorized_pickup" => "&cYou are not authorized to make chests portable in this area!",
            "not_authorized_place" => "&cYou are not authorized to place portable chests in this area!",
            "inventory_full" => "&cYour inventory is full! Cannot create portable chest!"
        ]);
        
        $this->chestNameColor = $this->getConfig()->get("chest-name-color", "&6");
        $this->chestName = $this->getConfig()->get("chest-name", "Portable Chest");
    }

    private function formatMessage(string $key): string {
        return str_replace("&", "§", $this->messages[$key] ?? "");
    }

    private function getFormattedChestName(): string {
        return str_replace("&", "§", $this->chestNameColor . $this->chestName);
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();

        if($block instanceof Chest) {
            if($player->isSneaking()) {
                $event->cancel();
                
                if(!$player->hasPermission("portablechests.use")) {
                    $player->sendMessage($this->formatMessage("not_authorized_pickup"));
                    return;
                }
                
                $portableChest = VanillaBlocks::CHEST()->asItem();
                $portableChest->setCustomName($this->getFormattedChestName());
                $nbt = $portableChest->getNamedTag();
                
                $tile = $block->getPosition()->getWorld()->getTile($block->getPosition());
                if($tile instanceof \pocketmine\block\tile\Chest) {
                    $inventory = $tile->getInventory();
                    $contents = [];
                    
                    foreach($inventory->getContents() as $slot => $item) {
                        $contents[$slot] = $item->nbtSerialize();
                    }
                    
                    $nbt->setString("CustomName", $this->chestName);
                    $nbt->setTag("ChestItems", new ListTag($contents));
                    $portableChest->setNamedTag($nbt);
                }
                
                if($player->getInventory()->canAddItem($portableChest)) {
                    $player->getInventory()->addItem($portableChest);
                    $block->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
                    $player->sendMessage($this->formatMessage("chest_made_portable"));
                } else {
                    $player->sendMessage($this->formatMessage("inventory_full"));
                    return;
                }
            }
        } else if($item->getCustomName() === $this->getFormattedChestName()) {
            $event->cancel();
            
            if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
                return;
            }
            
            if(!$player->hasPermission("portablechests.use")) {
                $player->sendMessage($this->formatMessage("not_authorized_place"));
                return;
            }
            
            $pos = $block->getPosition();
            if(!($block instanceof \pocketmine\block\SnowLayer) && !($block instanceof \pocketmine\block\TallGrass)) {
                $pos = $pos->add(0, 1, 0);
            }
            
            $world = $block->getPosition()->getWorld();
            
            $chest = VanillaBlocks::CHEST();
            $yaw = $player->getLocation()->getYaw();
            $facing = Facing::NORTH;
            
            if($yaw >= 315 || $yaw < 45) {
                $facing = Facing::NORTH;
            } elseif($yaw >= 45 && $yaw < 135) {
                $facing = Facing::EAST;
            } elseif($yaw >= 135 && $yaw < 225) {
                $facing = Facing::SOUTH;
            } elseif($yaw >= 225 && $yaw < 315) {
                $facing = Facing::WEST;
            }
            
            $chest->setFacing($facing);
            $world->setBlock($pos, $chest);
            
            $tile = $world->getTile($pos);
            if($tile instanceof \pocketmine\block\tile\Chest) {
                $nbt = $item->getNamedTag();
                if($nbt->getTag("ChestItems") !== null) {
                    $contents = $nbt->getListTag("ChestItems");
                    $inventory = $tile->getInventory();
                    
                    foreach($contents as $itemNbt) {
                        $inventory->addItem(Item::nbtDeserialize($itemNbt));
                    }
                }
            }
            
            $player->getInventory()->setItemInHand(VanillaBlocks::AIR()->asItem());
            
            $player->sendMessage($this->formatMessage("chest_placed"));
        }
    }
}