<?php
/**
 * Created by PhpStorm.
 * User: me
 * Date: 2018-02-14
 * Time: 08:19
 */

class User {
	private $id, $username, $firstname, $lastname, $position;

	protected function __construct(int $id, string $username, ?string $firstname, ?string $lastname, ?string $position) {
		$this->id 			= $id;
		$this->username 	= $username;
		$this->firstname 	= $firstname;
		$this->lastname	 	= $lastname;
		$this->position 	= $position;
	}
	
	public static function create(string $username, ?string $firstname, ?string $lastname, ?string $position) {
		return new self(null, $username, $firstname, $lastname, $position);
	}
	
	public function save() {
		$sql = DB::get();
		
		if ($this->exists()) {
			$res = $sql->query("UPDATE `users` SET (`firstname`='$this->firstname',`lastname`='$this->lastname',`position`='$this->position') WHERE `id`=".$this->id);
		} else {
			$res = $sql->query("INSERT INTO `users` (`firstname`,`lastname`,`position`,`username`) VALUES ('$this->firstname','$this->lastname','$this->position','$this->username')");
		}
		
		if ($sql->errno)
				throw new Exception("Error updating user in the database. MySQL said: ".$sql->error);
	}
	
	private function exists(): bool {
		if (!isset($this->id))
			return false;
		
		$sql = DB::get();
		
		$res = $sql->query("SELECT * FROM `users` WHERE `id`=".$this->id);
		
		if (!$res)
			throw new Exception("Error occurred in making SQL query. ".$sql->error);
		
		return $res->num_rows == 1;
	}
	
	public function getId(): int {
		return $this->id;
	}
	
	public function getUsername(): string {
		return $this->username;
	}
	
	public function getFirstname(): ?string {
		return $this->firstname;
	}
	
	public function getLastname(): ?string {
		return $this->lastname;
	}
	
	public function getPosition(): ?string {
		return $this->position;
	}

	public static function get(int $id): ?User {
		$sql = DB::get();
		
		$res = $sql->query("SELECT * FROM `users` WHERE `id`=$id");
		
		if ($res->num_rows == 1) {
			$row = $res->fetch_assoc();
			
			return new self($row["id"], $row["username"], $row["firstname"], $row["lastname"], $row["position"]);
		} else {
			throw new Exception("User with id $id not found in the database.");
		}
	}
}