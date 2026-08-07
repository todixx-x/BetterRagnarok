<?php

/*
 *
 *      _    _ _
 *     / \  | | |_ __ _ _   _
 *    / _ \ | | __/ _` | | | |
 *   / ___ \| | || (_| | |_| |
 *  /_/   \_\_|\__\__,_|\__, |
 *                       |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Original work by the PocketMine Team.
 * https://www.pocketmine.net/
 *
 * @author BetterRagnarok Team
 * @link https://github.com/todixx-x/BetterRagnarok
 *
 * Inlined from pmmp/DevTools (LGPL-3.0).
 */

declare(strict_types=1);

namespace pocketmine\utils\devtools\commands;

use pocketmine\command\CommandSender;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function count;

class HandlerListByPluginCommand extends DevToolsCommand{

	private const EVENT_PRIORITY_NAMES = [
		EventPriority::LOWEST => "LOWEST",
		EventPriority::LOW => "LOW",
		EventPriority::NORMAL => "NORMAL",
		EventPriority::HIGH => "HIGH",
		EventPriority::HIGHEST => "HIGHEST",
		EventPriority::MONITOR => "MONITOR",
	];

	public function __construct(){
		parent::__construct(
			"handlersbyplugin",
			"Lists all event handlers registered by a given plugin",
			"/handlersbyplugin <pluginName>",
			"devtools.command.handlersbyplugin"
		);
		$this->setUsage("/handlersbyplugin <pluginName>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) !== 1){
			return false;
		}

		$plugin = Server::getInstance()->getPluginManager()->getPlugin($args[0]);
		if($plugin === null){
			$sender->sendMessage(TextFormat::RED . "No plugin found with name " . $args[0]);
			return true;
		}

		$sender->sendMessage("--- Event handlers registered by plugin " . TextFormat::GREEN . $plugin->getName() . TextFormat::WHITE . " ---");
		foreach(HandlerListManager::global()->getAll() as $className => $handlerList){
			foreach(EventPriority::ALL as $priority){
				$priorityName = self::EVENT_PRIORITY_NAMES[$priority];
				$handlers = $handlerList->getListenersByPriority($priority);
				if(count($handlers) === 0){
					continue;
				}

				foreach($handlers as $handler){
					if($handler->getPlugin() !== $plugin){
						continue;
					}

					$sender->sendMessage(
						"- " .
						TextFormat::DARK_GREEN . Utils::getNiceClosureName($handler->getHandler()) . TextFormat::RESET .
						" handles event " .
						TextFormat::DARK_GREEN . $className . TextFormat::RESET .
						" at priority " .
						TextFormat::DARK_GREEN . $priorityName . TextFormat::RESET .
						" (handles cancelled events: " .
						TextFormat::DARK_GREEN . ($handler->isHandlingCancelled() ? "yes" : "no") . TextFormat::RESET .
						")"
					);
				}
			}
		}

		return true;
	}
}
