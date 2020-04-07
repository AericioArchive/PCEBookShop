<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop;

use Aericio\PCEBookShop\commands\BookShopCommand;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use DaPigGuy\libPiggyEconomy\exceptions\MissingProviderDependencyException;
use DaPigGuy\libPiggyEconomy\exceptions\UnknownProviderException;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use DaPigGuy\libPiggyEconomy\providers\EconomyProvider;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\plugin\PluginBase;

class PCEBookShop extends PluginBase
{
    /** @var EconomyProvider */
    public $economyProvider;

    /** @var array */
    public $enchantments = [];

    /**
     * @throws HookAlreadyRegistered
     * @throws MissingProviderDependencyException
     * @throws UnknownProviderException
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        libPiggyEconomy::init();
        $this->economyProvider = libPiggyEconomy::getProvider($this->getConfig()->get('economy'));

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("pcebookshop", new BookShopCommand($this, "pcebookshop", "Opens the PiggyCustomEnchants Book Shop Menu", ['bookshop', 'bs']));

        foreach (CustomEnchantManager::getEnchantments() as $enchants) $this->enchantments[$enchants->getRarity()][] = $enchants;
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
     * Adapted from https://stackoverflow.com/a/11872928
     */
    public function getRandomWeightedElement(int $max)
    {
        $weightedValues = [];
        for ($i = 1; $i <= $max; $i++) $weightedValues[] = $i;
        $rand = mt_rand(1, (int)array_sum($weightedValues));
        foreach ($weightedValues as $key => $value) {
            $rand -= $value;
            if ($rand <= 0) return $key;
        }
        return null;
    }
}