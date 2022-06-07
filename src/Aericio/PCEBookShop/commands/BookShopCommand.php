<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop\commands;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\ModalForm;
use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\SimpleForm;
use Aericio\PCEBookShop\PCEBookShop;
use pocketmine\command\CommandSender;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;

class BookShopCommand extends BaseCommand
{
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(PCEBookShop::getInstance()->getMessage("command.use-in-game"));
            return;
        }
        $this->sendShopForm($sender);
    }

    public function sendShopForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data): void {
            if ($data !== null) {
                $type = array_keys(Utils::RARITY_NAMES)[$data];
                $name = Utils::RARITY_NAMES[$type];
                $cost = PCEBookShop::getInstance()->getConfig()->getNested("cost." . strtolower($name));
                $form = new ModalForm(function (Player $player, ?bool $data) use ($cost, $name, $type): void {
                    if ($data !== null) {
                        if ($data) {
                            if ($player->getXpManager()->getXpLevel() < $cost) {
                                $player->sendMessage(PCEBookShop::getInstance()->getMessage("command.insufficient-funds", ["{AMOUNT}" => round($cost - $player->getXpManager()->getXpLevel(), 2, PHP_ROUND_HALF_DOWN)]));
                                return;
                            }
                            $item = ItemFactory::getInstance()->get(ItemIds::BOOK);
                            $item->setCustomName(TextFormat::RESET . PCEBookShop::getInstance()->getMessage("item.name", ["{COLOR_RARITY}" => Utils::getColorFromRarity($type), "{ENCHANTMENT}" => $name]) . TextFormat::RESET);
                            $item->setLore([PCEBookShop::getInstance()->getMessage("item.lore")]);
                            $item->getNamedTag()->setInt("ceshop", $type);
                            $inventory = $player->getInventory();
                            if ($inventory->canAddItem($item)) {
                                $player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - $cost);
                                $inventory->addItem($item);
                                return;
                            }
                            $player->sendMessage(PCEBookShop::getInstance()->getMessage("menu.confirmation.inventory-full"));
                        } else {
                            $this->sendShopForm($player);
                        }
                    }
                });
                $form->setTitle(PCEBookShop::getInstance()->getMessage("menu.confirmation.title"));
                $form->setContent(PCEBookShop::getInstance()->getMessage("menu.confirmation.content", ["{RARITY_COLOR}" => Utils::getColorFromRarity($type), "{ENCHANTMENT}" => $name, "{AMOUNT}" => round($cost, 2, PHP_ROUND_HALF_DOWN)]));
                $form->setButton1("Yes");
                $form->setButton2("No");
                $player->sendForm($form);
            }
        });
        $namee = $player->getName();
        $form->setTitle(PCEBookShop::getInstance()->getMessage("menu.title"));
        $form->setContent("§bHello, §e$namee\n\n§bHere You Can Get Custom Enchanment Books\n\n§bTap The Book Ground To Get Random Custom Enchanment");
        foreach (Utils::RARITY_NAMES as $rarity => $name) {
            $cost = PCEBookShop::getInstance()->getConfig()->getNested('cost.' . strtolower($name));
            $form->addButton(PCEBookShop::getInstance()->getMessage("menu.button", ["{RARITY_COLOR}" => Utils::getColorFromRarity($rarity), "{ENCHANTMENT}" => $name, "{AMOUNT}" => round($cost, 2, PHP_ROUND_HALF_DOWN)]), 1, "https://static.wikia.nocookie.net/minecraft_gamepedia/images/5/50/Book_JE2_BE2.png");
        }

        $player->sendForm($form);
    }

    protected function prepare(): void
    {
        $this->setPermission("pcebookshop.command.bookshop");
    }
}
