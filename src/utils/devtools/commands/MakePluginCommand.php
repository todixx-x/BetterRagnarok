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
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\devtools\FolderPluginLoader;
use function assert;
use function count;
use function date;
use function implode;
use function ini_get;
use function is_array;
use function php_ini_loaded_file;
use function realpath;
use function rtrim;
use function sprintf;
use function trim;
use const DEVTOOLS_PLUGIN_STUB;
use const DEVTOOLS_REQUIRE_FILE_STUB;
use const DIRECTORY_SEPARATOR;

class MakePluginCommand extends DevToolsCommand{

	public function __construct(){
		parent::__construct(
			"makeplugin",
			"Creates a Phar plugin from one in source code form",
			"/makeplugin <pluginName>",
			"devtools.command.makeplugin"
		);
		$this->setUsage("/makeplugin <pluginName>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(isset($args[0]) and $args[0] === "*"){
			$plugins = Server::getInstance()->getPluginManager()->getPlugins();
			$succeeded = $failed = [];
			$skipped = 0;
			foreach($plugins as $plugin){
				if(!$plugin->getPluginLoader() instanceof FolderPluginLoader){
					$skipped++;
					continue;
				}
				if($this->buildOne($sender, $plugin->getName())){
					$succeeded[] = $plugin->getName();
				}else{
					$failed[] = $plugin->getName();
				}
			}
			if(count($failed) > 0){
				$sender->sendMessage(TextFormat::RED . count($failed) . " plugin"
					. (count($failed) === 1 ? "" : "s") . " failed to build: " . implode(", ", $failed));
			}
			if(count($succeeded) > 0){
				$sender->sendMessage(TextFormat::GREEN . count($succeeded) . "/" . (count($plugins) - $skipped) . " plugin"
					. ((count($plugins) - $skipped) === 1 ? "" : "s") . " successfully built: " . implode(", ", $succeeded));
			}
			return true;
		}
		return $this->buildOne($sender, trim(implode(" ", $args)));
	}

	private function buildOne(CommandSender $sender, string $pluginName) : bool{
		if(ini_get('phar.readonly') !== '0'){
			$sender->sendMessage(TextFormat::RED . "This command requires \"phar.readonly\" to be set to 0. Set it in " . php_ini_loaded_file() . " and restart the server.");
			return true;
		}
		if($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof \pocketmine\plugin\Plugin)){
			$sender->sendMessage(TextFormat::RED . "Invalid plugin name, check the name case.");
			return false;
		}
		$description = $plugin->getDescription();

		if(!($plugin->getPluginLoader() instanceof FolderPluginLoader)){
			$sender->sendMessage(TextFormat::RED . "Plugin " . $description->getName() . " is not in folder structure.");
			return false;
		}

		$pharPath = $plugin->getDataFolder() . $description->getName() . "_v" . $description->getVersion() . ".phar";

		$reflection = new \ReflectionClass(\pocketmine\plugin\PluginBase::class);
		$file = $reflection->getProperty("file");
		$file->setAccessible(true);
		$pfile = rtrim($file->getValue($plugin), '/');
		$filePath = realpath($pfile);
		if($filePath === false){
			$sender->sendMessage(TextFormat::RED . "Plugin " . $description->getName() . " not found at $pfile (maybe deleted?)");
			return false;
		}
		$filePath = rtrim($filePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$metadata = \pocketmine\utils\devtools\generate_plugin_metadata_from_yml($filePath . "plugin.yml");
		assert($metadata !== null);

		$stubMetadata = [];
		foreach($metadata as $key => $value){
			$stubMetadata[] = addslashes(ucfirst($key) . ": " . (is_array($value) ? implode(", ", $value) : $value));
		}
		$stub = sprintf(DEVTOOLS_PLUGIN_STUB, $description->getName(), $description->getVersion(), \pocketmine\utils\devtools\DEVTOOLS_VERSION, date("r"), implode("\n", $stubMetadata));

		foreach(\pocketmine\utils\devtools\build_phar($pharPath, $filePath, [], $metadata, $stub, \Phar::SHA1, \Phar::GZ) as $line){
			$sender->sendMessage("[DevTools] $line");
		}
		$sender->sendMessage("Phar plugin " . $description->getName() . " v" . $description->getVersion() . " has been created on " . $pharPath);
		return true;
	}
}
