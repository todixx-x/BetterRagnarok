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
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function sprintf;

class PermissionListCommand extends DevToolsCommand{

	public function __construct(){
		parent::__construct(
			"listperms",
			"Lists all the permission nodes set on the executor, or a player",
			"/listperms [playerName]",
			"devtools.command.listperms.self"
		);
		$this->setPermission("devtools.command.listperms.self;devtools.command.listperms.other");
		$this->setUsage("/listperms [playerName]");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$target = $sender;
		if(isset($args[0])){
			if(($player = Server::getInstance()->getPlayerByPrefix($args[0])) instanceof Player){
				$target = $player;
			}else{
				return false;
			}
		}

		if($target !== $sender and !$sender->hasPermission("devtools.command.listperms.other")){
			$sender->sendMessage(TextFormat::RED . "You do not have permissions to check other players.");
			return true;
		}

		$sender->sendMessage(TextFormat::GOLD . "--- Permissions assigned to " . TextFormat::YELLOW . $target->getName() . TextFormat::GOLD . " ---");
		foreach($target->getEffectivePermissions() as $permissionAttachmentInfo){
			$sender->sendMessage("- " . self::describePermission($permissionAttachmentInfo));
		}
		return true;
	}

	private static function describePermission(PermissionAttachmentInfo $permInfo) : string{
		$permColor = static function(PermissionAttachmentInfo $info, bool $dark) : string{
			if($info->getValue()){
				$color = $dark ? TextFormat::DARK_GREEN : TextFormat::GREEN;
			}else{
				$color = $dark ? TextFormat::DARK_RED : TextFormat::RED;
			}
			return sprintf("%s%s%s", $color, $info->getPermission(), TextFormat::WHITE);
		};
		$permValue = static function(bool $value) : string{
			return ($value ? TextFormat::GREEN . "true" : TextFormat::RED . "false") . TextFormat::WHITE;
		};

		$groupPermInfo = $permInfo->getGroupPermissionInfo();
		if($groupPermInfo !== null){
			return $permColor($permInfo, false) . " is set to " . $permValue($permInfo->getValue()) . " by " . $permColor($groupPermInfo, true);
		}
		$permOrigin = $permInfo->getAttachment();
		if($permOrigin !== null){
			$originName = "plugin " . TextFormat::GREEN . $permOrigin->getPlugin()->getName();
		}else{
			$originName = "base permission";
		}
		return $permColor($permInfo, false) . " is set to " . $permValue($permInfo->getValue()) . " explicitly by $originName" . TextFormat::WHITE;
	}
}
