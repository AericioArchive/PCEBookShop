<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop\commands;

use Aericio\PCEBookShop\PCEBookShop;
use CortexPE\Commando\BaseCommand;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class BookShopCommand extends BaseCommand
{
    protected PCEBookShop $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage("command.use-in-game"));
            return;
        }
        $this->sendShopForm($sender);
    }

    public function sendShopForm(Player $player): void
    {
        $this->plugin = PCEBookShop::getInstance();
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data !== null) {
                $type = array_keys(Utils::RARITY_NAMES)[$data];
                $name = Utils::RARITY_NAMES[$type];
                $cost = $this->plugin->getConfig()->getNested("cost." . strtolower($name));
                $form = new ModalForm(function (Player $player, ?bool $data) use ($cost, $name, $type): void {
                    if ($data !== null) {
                        if ($data) {
                            $economyProvider = $this->plugin->getEconomyProvider();
                            $item = VanillaItems::BOOK();
                            $item->setCustomName(TextFormat::RESET . $this->plugin->getMessage("item.name", ["{COLOR_RARITY}" => Utils::getColorFromRarity($type), "{ENCHANTMENT}" => $name]) . TextFormat::RESET);
                            $item->setLore([$this->plugin->getMessage("item.lore")]);
                            $item->getNamedTag()->setInt("pcebookshop", $type);
                            $inventory = $player->getInventory();
                            if ($inventory->canAddItem($item)) {
                                $this->plugin->getEconomyProvider()->takeMoney($player, $cost, function (bool $success) use ($player, $item): void {
                                    if (!$success) {
                                        $player->sendMessage('You do not have enough money to buy this book.');
                                        return;
                                    }
                                    $player->getInventory()->addItem($item);
                                });
                                return;
                            }
                            $player->sendMessage($this->plugin->getMessage("menu.confirmation.inventory-full"));
                        } else {
                            $this->sendShopForm($player);
                        }
                    }
                });
                $form->setTitle($this->plugin->getMessage("menu.confirmation.title"));
                $form->setContent($this->plugin->getMessage("menu.confirmation.content", ["{RARITY_COLOR}" => Utils::getColorFromRarity($type), "{ENCHANTMENT}" => $name, "{AMOUNT}" => round($cost, 2, PHP_ROUND_HALF_DOWN)]));
                $form->setButton1("Yes");
                $form->setButton2("No");
                $player->sendForm($form);
            }
        });
        $form->setTitle($this->plugin->getMessage("menu.title"));
        foreach (Utils::RARITY_NAMES as $rarity => $name) {
            $cost = $this->plugin->getConfig()->getNested('cost.' . strtolower($name));
            $form->addButton($this->plugin->getMessage("menu.button", ["{RARITY_COLOR}" => Utils::getColorFromRarity($rarity), "{ENCHANTMENT}" => $name, "{AMOUNT}" => round($cost, 2, PHP_ROUND_HALF_DOWN)]));
        }
        $player->sendForm($form);
    }

    protected function prepare(): void
    {
        $this->setPermission("pcebookshop.command.bookshop");
    }
}