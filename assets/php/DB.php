<?php
class DB {
	private static $connections = [];
	private static $config;

	public static function get() {
		self::$config = json_decode(file_get_contents(__DIR__."/../config/database.json"), true);

		$sql = new mysqli(self::$config["host"], self::$config["username"], self::$config["password"], self::$config["database"]);
		$connections[] = $sql;
		return $sql;
	}

	public static function getDBEngine(): DBEngine {
		$sql = self::get();
		return new MySQL($sql);
	}

	public static function close_all() {
		/** @var mysqli $connection */
		foreach (self::$connections as $connection) {
			if (!$connection->close()) {
				throw new CampaignException("Unable to close MySQLi connection.");
			}
		}
	}
}