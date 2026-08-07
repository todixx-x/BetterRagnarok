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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function count;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function mkdir;
use function rtrim;
use function str_replace;
use function trim;
use const DIRECTORY_SEPARATOR;

class ExtractPluginCommand extends DevToolsCommand{

	public function __construct(){
		parent::__construct(
			"extractplugin",
			"Extracts the source code from a Phar plugin",
			"/extractplugin <pluginName>",
			"devtools.command.extractplugin"
		);
		$this->setUsage("/extractplugin <pluginName>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$pluginName = trim(implode(" ", $args));
		if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)){
			$sender->sendMessage(TextFormat::RED . "Invalid plugin name, check the name case.");
			return true;
		}
		$description = $plugin->getDescription();

		if(!($plugin->getPluginLoader() instanceof PharPluginLoader)){
			$sender->sendMessage(TextFormat::RED . "Plugin " . $description->getName() . " is not in Phar structure.");
			return true;
		}

		$folderPath = $plugin->getDataFolder() . DIRECTORY_SEPARATOR . $description->getName() . "_v" . $description->getVersion() . "/";
		if(file_exists($folderPath)){
			$sender->sendMessage("Plugin already exists, overwriting...");
		}else{
			@mkdir($folderPath);
		}

		$reflection = new \ReflectionClass(PluginBase::class);
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$pharPath = str_replace("\\", "/", rtrim($file->getValue($plugin), "\\/"));

		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
			$path = $fInfo->getPathname();
			@mkdir(dirname($folderPath . str_replace($pharPath, "", $path)), 0755, true);
			file_put_contents($folderPath . str_replace($pharPath, "", $path), file_get_contents($path));
		}
		$sender->sendMessage("Source plugin " . $description->getName() . " v" . $description->getVersion() . " has been created on " . $folderPath);
		return true;
	}
}
