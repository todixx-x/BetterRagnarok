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

use pocketmine\command\Command;
use pocketmine\utils\TextFormat;

/**
 * Base class for inlined DevTools commands. DevTools is now baked into the server,
 * so these commands don't implement PluginOwned — they have no parent plugin.
 */
abstract class DevToolsCommand extends Command{

	public function __construct(string $name, string $description, string $usageMessage, string $permission){
		parent::__construct($name, $description, $usageMessage, [$permission]);
	}

	protected function coloredHeader(string $title) : string{
		return TextFormat::GREEN . "---- " . TextFormat::WHITE . $title . TextFormat::GREEN . " ----";
	}
}
