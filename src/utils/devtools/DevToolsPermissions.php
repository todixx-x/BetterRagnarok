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

namespace pocketmine\utils\devtools;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

/**
 * Registers permission nodes used by the embedded DevTools commands. Must be
 * called before any DevTools command is constructed, otherwise Command::setPermission()
 * will throw "Cannot use non-existing permission".
 */
final class DevToolsPermissions{

	private function __construct(){
		//no instances
	}

	public static function register() : void{
		$manager = PermissionManager::getInstance();

		$manager->addPermission(new Permission(
			"devtools.command.makeplugin",
			"Allows the use of /makeplugin"
		));
		$manager->addPermission(new Permission(
			"devtools.command.extractplugin",
			"Allows the use of /extractplugin"
		));
		$manager->addPermission(new Permission(
			"devtools.command.genplugin",
			"Allows the use of /genplugin"
		));
		$manager->addPermission(new Permission(
			"devtools.command.handlers",
			"Allows the use of /handlers"
		));
		$manager->addPermission(new Permission(
			"devtools.command.handlersbyplugin",
			"Allows the use of /handlersbyplugin"
		));
		$manager->addPermission(new Permission(
			"devtools.command.listperms.self",
			"Allows the use of /listperms on self"
		));
		$manager->addPermission(new Permission(
			"devtools.command.listperms.other",
			"Allows the use of /listperms on other players"
		));
		$manager->addPermission(new Permission(
			"devtools.command.checkperm",
			"Allows the use of /checkperm on self"
		));
		$manager->addPermission(new Permission(
			"devtools.command.checkperm.other",
			"Allows the use of /checkperm on other players"
		));
	}
}