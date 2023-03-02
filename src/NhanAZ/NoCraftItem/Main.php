<?php

declare(strict_types=1);

namespace NhanAZ\NoCraftItem;

use NhanAZ\libBedrock\StringToItem;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

	protected function onEnable(): void {
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		foreach ($this->getConfig()->get("noCraftItem") as $noCraftItem) {
			StringToItem::parse($noCraftItem);
		}
	}

	private function sendCancelMessage(Player $player): void {
		$cancelMessage = $this->getConfig()->get("cancelMessage");
		if (!empty($cancelMessage)) {
			$player->sendMessage($cancelMessage);
		}
	}

	public function onCraft(CraftItemEvent $event): void {
		$player = $event->getPlayer();
		$outputs = $event->getOutputs();
		$config = $this->getConfig();
		if ($player->hasPermission("nocraftitem.bypass")) {
			return;
		}
		if ($config->get("noCraftAllItem")) {
			$event->cancel();
			self::sendCancelMessage($player);
			return;
		}
		foreach ($outputs as $output) {
			foreach ($config->get("noCraftItem") as $noCraftItem) {
				if ($output->equals(StringToItem::parse($noCraftItem))) {
					$event->cancel();
					self::sendCancelMessage($player);
					break;
				}
			}
		}
	}
}
