<?php
require_once("DB.php");

class Endorsement {
	private $sender;
	private $sent;
	private $content;
	private $title;
	private $id;
	private $active;
	private $ignored;

	private function __construct($id, $title, $content, $from, $sent, $active, $ignored) {
		$this->id = $id;
		$this->title = $title;
		$this->content = $content;
		$this->sender = $from;
		$this->sent = $sent;
		$this->active = $active;
		$this->ignored = $ignored;
	}

	public static function get_all(): array {
		$sql = DB::get();

		$res = $sql->query("SELECT * FROM `endorsements`");

		if ($res->num_rows == 0)
			return [];

		$ret = [];
		while ($row = $res->fetch_assoc()) {
			$ret[] = new self($row["id"], $row["title"], $row["content"], $row["from"], $row["submitted"], $row["active"], $row["ignored"]);
		}

		return $ret;
	}

	public function getID() {
		return $this->id;
	}

	public function getSender() {
		return $this->sender;
	}

	public function getContent() {
		return $this->content;
	}

	public function getTitle() {
		return $this->title;
	}

	public function isActive() {
		return $this->active;
	}

	public function isIgnored() {
		return $this->ignored;
	}

	public static function get(int $id): ?Endorsement {
		$sql = DB::get();

		$res = $sql->query("SELECT * FROM `endorsements` WHERE `id`=$id");

		if ($res && $res->num_rows != 0) {
			$row = $res->fetch_assoc();

			return new self($row["id"], $row["title"], $row["content"], $row["from"], $row["submitted"], $row["active"], $row["ignored"]);
		} else return null;
	}

	public function approve() {
		$sql = DB::get();

		$sql->query("UPDATE `endorsements` SET `active`=1 WHERE `id`=".$this->id);
	}

	public function ignore() {
		$sql = DB::get();

		$sql->query("UPDATE `endorsements` SET `ignored`=1 WHERE `id`=".$this->id);
	}

	public function resolve(string $resolution) {
		switch ($resolution) {
			case "approve": {
				$this->approve();
				return;
			}
			case "ignore": {
				$this->ignore();
				return;
			}
		}
	}

	public static function create(string $title, string $content, string $sender): ?Endorsement {
		$sql = DB::get();

		$title = $sql->escape_string($title);
		$content = $sql->escape_string($content);
		$sender = $sql->escape_string($sender);

		$sql->query("INSERT INTO `endorsements` (`title`,`content`,`from`) VALUES ('$title','$content','$sender')");

		if ($sql->errno)
			return null;

		$e = self::get($sql->insert_id);

		if (!$e) {
			return null;
		}

		return $e;
	}
}