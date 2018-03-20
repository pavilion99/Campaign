<?php
require_once("DB.php");

class Alert {
	private $name;
	private $content;
	private $sent_by;
	private $date;
	private $id;

	private function __construct(int $id, string $name, string $content, DateTime $date, string $sent_by) {
		$this->name = $name;
		$this->content = $content;
		$this->sent_by = $sent_by;
		$this->date = $date;
		$this->id = $id;
	}

	public static function get_all(): array {
		$sql = DB::get();

		$res = $sql->query("SELECT * FROM `alerts`");

		if ($res->num_rows == 0) {
			return [];
		} else {
			$return = [];

			while ($row = $res->fetch_assoc()) {
				$return[] = new self($row["id"], $row["title"], $row["content"], new DateTime($row["sent"]), $row["sent_by"]);
			}

			return $return;
		}
	}

	public function render() {
		return '
			<div class="mdc-card">
				<section class="mdc-card__primary">
					<h1 class="mdc-card__title mdc-card__title--large">'.$this->name.'</h1>
					<h2 class="mdc-card__subtitle">Sent by '.$this->sent_by.' on '.$this->date->format("Y-m-d").' at '.$this->date->format("H:i:s").'.</h2>
				</section>
				<section class="mdc-card__supporting-text">
					'.$this->content.'	
				</section>
				<section class="mdc-card__actions">
					<form action="'.Config::get("app-root").'alerts/send" method="POST">
						<input type="hidden" name="message" value="'.$this->content.'">
						<input type="hidden" name="title" value="'.$this->name.'">
						<button type="submit" class="mdc-button mdc-button--compact mdc-card__action">Resend</button>
					</form>
				</section>  
			</div>
		';
	}

	public static function create(string $content, string $by, string $title = "") {

	}

	public function save(): bool {
		$sql = DB::get();

		if ($this->id) {
			$id = $this->id;

			$by = $sql->escape_string($this->sent_by);
			$content = $sql->escape_string($this->content);
			$title = $sql->escape_string($this->name);

			$res = $sql->query("UPDATE `alerts` SET (`sent_by`='$by',`title`='$title',`content`='$content') WHERE `id`=$id");

			return !(!$res || $sql->errno);
		} else {
			$by = $sql->escape_string($this->sent_by);
			$content = $sql->escape_string($this->content);
			$title = $sql->escape_string($this->name);
			$res = $sql->query("INSERT INTO `alerts` (`sent_by`,`title`,`content`) VALUES ('$by','$title','$content')");

			if ($res)
				$this->id = $sql->insert_id;

			return !(!$res || $sql->errno);
		}
	}
}