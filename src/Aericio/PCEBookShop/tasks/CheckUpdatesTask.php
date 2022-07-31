<?php

declare(strict_types=1);

namespace Aericio\PCEBookShop\tasks;

use Aericio\PCEBookShop\PCEBookShop;
use Exception;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\plugin\ApiVersion;
use pocketmine\utils\Internet;

class CheckUpdatesTask extends AsyncTask
{
    public function onRun(): void
    {
        $this->setResult([Internet::getURL("https://poggit.pmmp.io/releases.json?name=PCEBookShop", 10, [], $error), $error]);
    }

    public function onCompletion(): void
    {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin("PCEBookShop");
        $logger = Server::getInstance()->getLogger();
        [$body, $error] = $this->getResult();
        if ($error) {
            $logger->warning("Auto-update check failed.");
            $logger->debug($error);
        } else {
            $versions = json_decode($body, true);
            if ($versions) foreach ($versions as $version) {
                if (version_compare($plugin->getDescription()->getVersion(), $version["version"]) === -1) {
                    if (ApiVersion::isCompatible(Server::getInstance()->getApiVersion(), $version["api"][0])) {
                        $logger->notice("PCEBookShop v" . $version["version"] . " is available for download at " . $version["artifact_url"] . "/PCEBookShop.phar");
                        break;
                    }
                }
            }
        }
    }
}