<?php
require_once("DB.php");

class Config {
	private static $cache = [];

	public static function get(string $name): ?string {
		if (empty(self::$cache)) {
			$sql = DB::get();

			$result = $sql->query("SELECT * FROM `setup`");

			while ($row = $result->fetch_assoc()) {
				self::$cache[$row["name"]] = $row["data"];
			}

			if (array_key_exists($name, self::$cache)) {
				return self::$cache[$name];
			} else {
				return null;
			}
		}

		if (array_key_exists($name, self::$cache))
			return self::$cache[$name];

		$sql = DB::get();

		$result = $sql->query("SELECT * FROM `setup` WHERE `name`='$name'");

		if ($result->num_rows == 0)
			return null;
		else {
			self::$cache[$name] = $name;
			return $result->fetch_assoc()["data"];
		}
	}
}