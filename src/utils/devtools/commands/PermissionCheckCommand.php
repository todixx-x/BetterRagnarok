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
use pocketmine\lang\Translatable;
use pocketmine\permission\Permissible;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function sprintf;
use function strtolower;

class PermissionCheckCommand extends DevToolsCommand{

	public function __construct(){
		parent::__construct(
			"checkperm",
			"Checks a permission value for the current sender, or a player",
			"/checkperm <node> [playerName]",
			"devtools.command.checkperm"
		);
		$this->setPermission("devtools.command.checkperm;devtools.command.checkperm.other");
		$this->setUsage("/checkperm <node> [playerName]");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			return false;
		}
		$node = strtolower($args[0]);
		$target = $sender;
		if(isset($args[1])){
			if(($player = Server::getInstance()->getPlayerByPrefix($args[1])) instanceof Player){
				$target = $player;
			}else{
				return false;
			}
		}

		if($target !== $sender and !$sender->hasPermission("devtools.command.checkperm.other")){
			$sender->sendMessage(TextFormat::RED . "You do not have permissions to check other players.");
			return true;
		}

		$sender->sendMessage($this->coloredHeader("Permission node " . $node));
		$perm = PermissionManager::getInstance()->getPermission($node);
		$message = "";
		if($perm instanceof Permission){
			$description = $perm->getDescription();
			$rawDescription = $description instanceof Translatable ? $sender->getLanguage()->translate($description) : $description;
			$message = TextFormat::GOLD . "Description: " . TextFormat::WHITE . $rawDescription . "\n";
			$children = [];
			foreach($perm->getChildren() as $name => $isGranted){
				$children[] = ($isGranted ? TextFormat::GREEN : TextFormat::RED) . $name . TextFormat::WHITE;
			}
			$message .= TextFormat::GOLD . "Children: " . TextFormat::WHITE . implode(", ", $children) . "\n";
		}else{
			$message = TextFormat::RED . "Permission does not exist\n";
		}
		$sender->sendMessage($message);
		$coloredName = TextFormat::YELLOW . $target->getName() . TextFormat::RESET;
		$sender->sendMessage(TextFormat::GOLD . "Permission info for $coloredName:");
		foreach(self::describePermissionSet($target, $node) as $line){
			$sender->sendMessage("- " . $line);
		}
		return true;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	private static function describePermissionSet(Permissible $sender, string $permission) : array{
		$permInfo = $sender->getEffectivePermissions()[$permission] ?? null;
		if($permInfo === null){
			return [
				TextFormat::RED . $permission . TextFormat::WHITE . " is not set (default " . TextFormat::RED . "false" . TextFormat::WHITE . ")"
			];
		}
		$result = [];
		while($permInfo !== null){
			$result[] = self::describePermission($permInfo);
			$permInfo = $permInfo->getGroupPermissionInfo();
		}
		return $result;
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
