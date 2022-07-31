<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop;

use Aericio\PCEBookShop\commands\BookShopCommand;
use Aericio\PCEBookShop\utils\Utils;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class PCEBookShop extends PluginBase
{
    private Config $messages;
    public EconomyProvider $economyProvider;
    public array $enchantments = [];
    use SingletonTrait;

    /**
     * @throws HookAlreadyRegistered
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    public function onEnable(): void
    {
        self::setInstance($this);
        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder() . "messages.yml");
        $this->saveDefaultConfig();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get("economy"));

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("pcebookshop", new BookShopCommand($this, "pcebookshop", "Opens the PiggyCustomEnchants Book Shop Menu", ["bookshop", "bs"]));

        foreach (CustomEnchantManager::getEnchantments() as $enchants) {
            $excluded = $this->getConfig()->get("excluded-enchants", []);
            if (!in_array($enchants->getId(), $excluded) && !in_array(strtolower($enchants->getName()), $excluded)) {
                $this->enchantments[$enchants->getRarity()][] = $enchants;
            }
        }
    }

    public function getMessage(string $key, array $tags = []): string
    {
        return Utils::translateColorTags(str_replace(array_keys($tags), $tags, $this->messages->getNested($key, $key)));
    }

    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }

    public function getEnchantmentsByRarity(int $rarity): array
    {
        return $this->enchantments[$rarity];
    }

    /**
     * Adapted from https://stackoverflow.com/a/445363
     */
    public function getRandomWeightedElement(int $max): int
    {
        return intval(floor(1 + pow(lcg_value(), $this->getConfig()->getNested('chance.gamma', 1.5)) * $max));
    }
}