<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
    /** @var PCEBookShop */
    private $plugin;

    public function __construct(PCEBookShop $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerInteractEvent(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($item->getId() !== Item::BOOK) return;
        if ($item->getNamedTag()->hasTag("pcebookshop")) {
            $nbt = $item->getNamedTag()->getInt("pcebookshop");
            $enchants = $this->plugin->getEnchantmentsByRarity($nbt);
            $enchant = $enchants[array_rand($enchants)];
            if ($enchant instanceof Enchantment) {
                $item = Item::get(Item::ENCHANTED_BOOK);
                $item->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Enchanted Book" . TextFormat::RESET);
                $item->addEnchantment(new EnchantmentInstance($enchant, $this->plugin->getRandomWeightedElement($enchant->getMaxLevel())));
                $inventory = $player->getInventory();
                if ($inventory->canAddItem($item)) {
                    $inventory->removeItem($inventory->getItemInHand()->pop());
                    $inventory->addItem($item->pop());
                    return;
                }
                $player->sendMessage(TextFormat::RED . "Your inventory is full.");
            }
        }
    }
}