<?php
require_once("DB.php");
require_once("Addon.php");

class Permission {
	private static $permission_defaults = [
		"alerts.send" => 0,
		"alerts.list" => 1,
		"contacts.register" => 1,
		"contacts.delete" => 1,
		"contacts.edit" => 1,
		"contacts.list" => 1,
		"users.list" => 1,
		"users.delete" => 0,
		"users.edit" => 0,
		"users.create" => 0,
		"endorsements.pending" => 1,
		"endorsements.resolve" => 0,
		"interactions.create" => 1,
		"interactions.delete" => 0,
		"interactions.edit" => 0,
		"interactions.list" => 1,
		"contacts.superuser" => 0
	];

	private static $permission_descriptions = [
		"alerts.send" => "Send Text Alerts",
		"alerts.list" => "View Alert History",
		"contacts.register" => "Register New Contacts",
		"contacts.delete" => "Delete Contacts",
		"contacts.edit" => "Edit Existing Contacts",
		"contacts.list" => "View All Contacts",
		"users.list" => "List All Users",
		"users.delete" => "Delete Users",
		"users.edit" => "Edit Existing Users",
		"users.create" => "Create New Users",
		"endorsements.pending" => "View Pending Endorsements",
		"endorsements.resolve" => "Accept/Ignore Pending Endorsements",
		"interactions.create" => "Create Interactions",
		"interactions.list" => "List All Interactions",
		"interactions.delete" => "Delete Interactions",
		"interactions.edit" => "Edit Interactions",
		"contacts.superuser" => "View Unlimited Contacts"
	];

	private $name;
	private $default;

	private function __construct(string $name, int $default) {
		$this->name = $name;
		$this->default = $default;
	}

	public static function get_known_permissions(): array {
		$perms = [];

		foreach (self::$permission_defaults as $key => $value) {
			$perms[] = new self($key, $value);
		}

		$addons = Addon::get_all();

		foreach ($addons as $addon) {
			/** @var $addon Addon */
			$addonPerms = $addon->get_permissions();

			foreach ($addonPerms as $addonPerm => $default) {
				$perms[] = new Permission($addonPerm, $default);
			}
		}

		return $perms;
	}

	public static function get_permission(string $name): ?Permission {
		$perms = self::get_known_permissions();

		foreach ($perms as $prem) {
			/** @var $perm Permission */
			if ($prem->getName() == $name)
				return $prem;
		}

		return null;
	}

	public function has_this_permission(): int {
		return self::has_permission($this->name);
	}

	public static function has_permission(string $permission): int {
		return self::user_has_permission($_SESSION["id"], $permission);
	}

	public function user_has_this_permission(int $user): int {
		return self::user_has_permission($user, $this->getName());
	}

	public static function user_has_permission(int $user, string $permission): int {
		$sql = DB::get();
		$res = $sql->query("SELECT * FROM `permissions` WHERE `permission`='".$permission."' AND `user`=".$user);

		if ($res->num_rows == 0) {
			if (!self::permission_is_known($permission))
				return false;

			$def = self::get_permission($permission)->getDefault();

			$sql->query("INSERT INTO `permissions` (`user`,`permission`,`value`) VALUES ($user,'$permission',$def)");
			return $def;
		} else {
			return $res->fetch_assoc()["value"];
		}
	}

	public static function permission_is_known(string $perm): bool {
		$perms = self::get_known_permissions();

		foreach ($perms as $prem) {
			/** @var $perm Permission */
			if ($prem->getName() == $perm)
				return true;
		}

		return false;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getDescription(): ?string {
		return self::$permission_descriptions[$this->name];
	}

	public function getDefault(): int {
		return $this->default;
	}
}