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

const DEVTOOLS_VERSION = "1.17.3+dev";

const DEVTOOLS_REQUIRE_FILE_STUB = '<?php require("phar://" . __FILE__ . "/%s"); __HALT_COMPILER();';
const DEVTOOLS_PLUGIN_STUB = '
<?php
echo "PocketMine-MP plugin %s v%s
This file has been generated using DevTools v%s at %s
----------------
%s
";
__HALT_COMPILER();
';

/**
 * @param string[] $strings
 * @param string|null $delim
 *
 * @return string[]
 */
function preg_quote_array(array $strings, ?string $delim = null) : array{
	return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
}

/**
 * @param string[]    $includedPaths
 * @param mixed[]     $metadata
 * @phpstan-param array<string, mixed> $metadata
 *
 * @return \Generator|string[]
 */
function build_phar(string $pharPath, string $basePath, array $includedPaths, array $metadata, string $stub, int $signatureAlgo = \Phar::SHA1, ?int $compression = null){
	$basePath = rtrim(str_replace("/", \DIRECTORY_SEPARATOR, $basePath), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
	$includedPaths = array_map(function($path) : string{
		$path = rtrim(str_replace("/", \DIRECTORY_SEPARATOR, $path), \DIRECTORY_SEPARATOR);
		return \is_dir($path) ? $path . \DIRECTORY_SEPARATOR : $path;
	}, $includedPaths);
	if(\file_exists($pharPath)){
		yield "Phar file already exists, overwriting...";
		try{
			\Phar::unlinkArchive($pharPath);
		}catch(\PharException $e){
			//unlinkArchive() doesn't like dodgy phars
			\unlink($pharPath);
		}
	}

	yield "Adding files...";

	$start = \microtime(true);
	$phar = new \Phar($pharPath);
	$phar->setMetadata($metadata);
	$phar->setStub($stub);
	$phar->setSignatureAlgorithm($signatureAlgo);
	$phar->startBuffering();

	//If paths contain any of these, they will be excluded
	$excludedSubstrings = preg_quote_array([
		\realpath($pharPath), //don't add the phar to itself
	], '/');

	$folderPatterns = preg_quote_array([
		\DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR,
		\DIRECTORY_SEPARATOR . '.' //"Hidden" files, git dirs etc
	], '/');

	//Only exclude these within the basedir, otherwise the project won't get built if it itself is in a directory that matches these patterns
	$basePattern = preg_quote(rtrim($basePath, \DIRECTORY_SEPARATOR), '/');
	foreach($folderPatterns as $p){
		$excludedSubstrings[] = $basePattern . '.*' . $p;
	}

	$regex = \sprintf('/^(?!.*(%s))^%s(%s).*/i',
		 \implode('|', $excludedSubstrings), //String may not contain any of these substrings
		 preg_quote($basePath, '/'), //String must start with this path...
		 \implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
	);

	$directory = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
	$iterator = new \RecursiveIteratorIterator($directory);
	$regexIterator = new \RegexIterator($iterator, $regex);

	$count = \count($phar->buildFromIterator($regexIterator, $basePath));
	yield "Added $count files";
	$phar->stopBuffering();

	if($compression !== null){
		yield "Checking for compressible files...";
		//foreach doesn't work properly when buildFromIterator was used, so we have to recreate the object
		$phar = new \Phar($pharPath);
		foreach(new \RecursiveIteratorIterator($phar) as $file => $finfo){
			/** @var \PharFileInfo $finfo */
			if($finfo->getSize() > (1024 * 512)){
				yield "Compressing " . $finfo->getFilename();
				$finfo->compress($compression);
			}
		}
	}

	yield "Done in " . \round(\microtime(true) - $start, 3) . "s";
}

/**
 * @return mixed[]|null
 * @phpstan-return array<string, mixed>|null
 */
function generate_plugin_metadata_from_yml(string $pluginYmlPath) : ?array{
	if(!\file_exists($pluginYmlPath)){
		return null;
	}

	$pluginYml = \yaml_parse_file($pluginYmlPath);
	return [
		"name" => $pluginYml["name"],
		"version" => $pluginYml["version"],
		"main" => $pluginYml["main"],
		"api" => $pluginYml["api"],
		"depend" => $pluginYml["depend"] ?? "",
		"description" => $pluginYml["description"] ?? "",
		"authors" => $pluginYml["authors"] ?? "",
		"website" => $pluginYml["website"] ?? "",
		"creationDate" => \time()
	];
}
