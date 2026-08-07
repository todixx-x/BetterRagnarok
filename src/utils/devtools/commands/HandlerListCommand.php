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
use pocketmine\event\HandlerList;
use pocketmine\event\HandlerListManager;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function count;
use function ksort;
use function str_contains;
use const SORT_STRING;

class HandlerListCommand extends DevToolsCommand{

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
			"handlers",
			"Lists all event handlers currently associated with an event",
			"/handlers <event>",
			"devtools.command.handlers"
		);
		$this->setUsage("/handlers <event>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) > 1){
			return false;
		}
		$all = HandlerListManager::global()->getAll();
		ksort($all, SORT_STRING);
		$found = false;
		foreach($all as $className => $handlerList){
			if(count($args) === 0 || str_contains($className, $args[0])){
				$found = true;
				self::describeHandlerList($sender, $handlerList, $className);
			}
		}
		if(!$found){
			$sender->sendMessage(TextFormat::RED . "No event handlers found for any classes containing \"" . $args[0] . "\"");
		}
		return true;
	}

	private static function describeHandlerList(CommandSender $sender, HandlerList $handlerList, string $className) : bool{
		$found = false;
		foreach(EventPriority::ALL as $priority){
			$priorityName = self::EVENT_PRIORITY_NAMES[$priority];
			$handlers = $handlerList->getListenersByPriority($priority);
			if(count($handlers) === 0){
				continue;
			}

			if(!$found){
				$found = true;
				$sender->sendMessage("--- Handlers called by " . TextFormat::GREEN . $className . TextFormat::WHITE . " ---");
			}

			foreach($handlers as $handler){
				$sender->sendMessage(
					"- " .
					TextFormat::DARK_GREEN . Utils::getNiceClosureName($handler->getHandler()) . TextFormat::RESET .
					" in plugin " .
					TextFormat::DARK_GREEN . $handler->getPlugin()->getName() . TextFormat::RESET .
					" at priority " .
					TextFormat::DARK_GREEN . $priorityName . TextFormat::RESET .
					" (handles cancelled events: " .
					TextFormat::DARK_GREEN . ($handler->isHandlingCancelled() ? "yes" : "no") . TextFormat::RESET .
					")"
				);
			}
		}

		return $found;
	}
}
