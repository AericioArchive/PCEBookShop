<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop\commands;

use Aericio\PCEBookShop\PCEBookShop;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BookShopCommand extends BaseCommand
{
    /** @var PCEBookShop */
    private $plugin;

    public function __construct(PCEBookShop $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Command must be used in-game.");
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
                $cost = $this->plugin->getConfig()->getNested('cost.' . strtolower($name));
                $form = new ModalForm(function (Player $player, ?bool $data) use ($cost, $name, $type): void {
                    if ($data !== null) {
                        if ($data) {
                            $economyProvider = $this->plugin->getEconomyProvider();
                            if ($economyProvider->getMoney($player) < $cost) {
                                $player->sendMessage(TextFormat::RED . "Insufficient funds. You need " . $economyProvider->getMonetaryUnit() . ($cost - $economyProvider->getMoney($player)) . " more.");
                                return;
                            }
                            $item = Item::get(Item::BOOK);
                            $item->setCustomName(TextFormat::RESET . Utils::getColorFromRarity($type) . $name . " Custom Enchants Book" . TextFormat::RESET);
                            $item->setLore(["Tap the ground to get a random custom enchantment."]);
                            $item->getNamedTag()->setInt("pcebookshop", $type);
                            if ($player->getInventory()->canAddItem($item)) {
                                $economyProvider->takeMoney($player, $cost);
                                $player->getInventory()->addItem($item);
                                return;
                            }
                            $player->sendMessage(TextFormat::RED . "Purchase cancelled. Your inventory is full.");
                        } else {
                            $this->sendShopForm($player);
                        }
                    }
                });
                $form->setTitle(TextFormat::GREEN . "PCEBookShop - Purchase Confirmation");
                $form->setContent("Are you sure you want to purchase " . Utils::getColorFromRarity($type) . $name . " Custom Enchants Book" . TextFormat::RESET . " for $" . $cost . "?");
                $form->setButton1("Yes");
                $form->setButton2("No");
                $player->sendForm($form);
                return;
            }
        });
        $form->setTitle(TextFormat::GREEN . "PCEBookShop - Menu");
        foreach (Utils::RARITY_NAMES as $rarity => $name) {
            $cost = $this->plugin->getConfig()->getNested('cost.' . strtolower($name));
            $form->addButton(Utils::getColorFromRarity($rarity) . $name . TextFormat::EOL . TextFormat::RESET . "Cost: " . $this->plugin->getEconomyProvider()->getMonetaryUnit() . $cost);
        }
        $player->sendForm($form);
        return;
    }

    /**
     * @throws ArgumentOrderException
     */
    protected function prepare(): void
    {
        $this->setPermission("pcebookshop.command.bookshop");
    }
}