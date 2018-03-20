<?php
require_once("Config.php");

class Addon {
	private $directory;
	private $config;

	private function __construct(string $directory) {
		$this->directory = $directory;
		$this->config = json_decode(file_get_contents($directory."/addon.json"), true);
	}

	public static function get_all() {
		$dirs = glob(Config::get("app-root-internal")."addons/*", GLOB_ONLYDIR);

		$return = [];
		foreach ($dirs as $dir) {
			$return[] = new Addon($dir);
		}

		return $return;
	}

	public function get_infocards(): array {
		$return = [];

		foreach ($this->config["infocards"] as $infocard) {
			$return[] = new Infocard($infocard["title"], $infocard["text"], $infocard["permission"], Config::get("app-root")."module/?a=".basename($this->directory));
		}

		return $return;
	}

	public function get_permissions(): array {
		return $this->config["permissions"];
	}

	public function get_by_name(string $name) {
		$dir = Config::get("app-root-internal")."addons/$name/";
		$configFile = $dir."addon.json";

		if (!file_exists($configFile))
			return null;

		return new self($dir);
	}
}