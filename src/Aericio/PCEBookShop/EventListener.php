<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{

    public function __construct(private PCEBookShop $plugin)
    {
    }

    public function onPlayerInteractEvent(PlayerInteractEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        if ($item->getId() !== ItemIds::BOOK) return;
        if ($item->getNamedTag()->getTag("pcebookshop") !== null) {
            $event->cancel();
            $nbt = $item->getNamedTag()->getInt("pcebookshop");
            $enchants = $this->plugin->getEnchantmentsByRarity($nbt);
            $enchant = $enchants[array_rand($enchants)];
            if ($enchant instanceof Enchantment) {
                $item =  ItemFactory::getInstance()->get(ItemIds::ENCHANTED_BOOK);
                $item->setCustomName(TextFormat::RESET . $this->plugin->getMessage("item.unused-name") . TextFormat::RESET);
                $item->addEnchantment(new EnchantmentInstance($enchant, $this->plugin->getRandomWeightedElement($enchant->getMaxLevel())));
                $inventory = $player->getInventory();
                if ($inventory->canAddItem($item)) {
                    $inventory->removeItem($inventory->getItemInHand()->pop());
                    $inventory->addItem($item->pop());
                    return;
                }
                $player->sendMessage($this->plugin->getMessage("inventory-full"));
            }
        }
    }
}
