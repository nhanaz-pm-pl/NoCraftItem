<?php

declare(strict_types=1);

namespace NhanAZ\NoCraftItem;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

	protected function onEnable(): void {
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		foreach ($this->getConfig()->get("noCraftItem") as $noCraftItem) {
			self::stringToItem($noCraftItem);
		}
	}

	private function stringToItem(string $string): Item {
		try {
			$item = StringToItemParser::getInstance()->parse($string) ?? LegacyStringToItemParser::getInstance()->parse($string);
		} catch (LegacyStringToItemParserException $e) {
			throw new LegacyStringToItemParserException($e->getMessage());
		}
		return $item;
	}

	private function sendCancelMessage(Player $player): void {
		$cancelMessage = $this->getConfig()->get("cancelMessage");
		if (!empty($cancelMessage)) {
			$player->sendMessage(TextFormat::colorize($cancelMessage));
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
				if ($output->equals(self::stringToItem($noCraftItem))) {
					$event->cancel();
					self::sendCancelMessage($player);
					break;
				}
			}
		}
	}
}
