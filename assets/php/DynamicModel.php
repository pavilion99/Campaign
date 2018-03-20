<?php
abstract class DynamicModel {
	/** @var DBEngine */
	protected static $db;

	protected $id;
	protected $values;

	private static $consonants = "bcdfghjklmnpqrstvwxz";
	private static $vowels = "aeiou";
	private static $sibilants = ["ch", "s", "sh", "x", "z"];
	private static $order = null;

	private static $custom = [
		"alga" => "algae",
		"alumnus" => "alumni",
		"larva" => "larvae",
		"crisis" => "crises",
		"analysis" => "analyses",
		"neurosis" => "neuroses",
		"paparazzo" => "paparazzi",
		"man" => "men",
		"woman" => "women",
		"child" => "children",
		"mouse" => "mice",
		"tooth" => "teeth",
		"goose" => "geese",
		"foot" => "feet",
		"ox" => "oxen",
        "person" => "people"
	];

	public static function getName(): string {
	    $baseClass = explode("\\", get_called_class());
		$nm = array_pop($baseClass);

        $string = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $nm));
        return $string;
	}

	public static function getPluralName(string $tst = null): string {
		$name = $tst == null ? self::getName() : $tst;
		$len = strlen($name);

		$last = substr($name, $len - 1, 1);
		$last2 = substr($name, $len - 2, 2);

		if (array_key_exists($name, self::$custom)) {
			return self::$custom[$name];
		}

		if ($last === "y") {
			if (strpos(self::$consonants, substr($name, $len - 2, 1)) !== false) {
				return substr($name, 0, $len - 1)."ies";
			} else {
				return $name."s";
			}
		} else if ($last === "f" || substr($name, $len-2, 2) === "fe") {
			$test = null;
			if ($last === "f") {
				$test = substr($name, $len - 2, 1);
			} else if ($last2 === "fe") {
				$test = substr($name, $len - 3, 1);
			}
			if (strpos(self::$consonants, $test) !== false) {
				if ($last === "f") {
					return substr($name, 0, $len - 1)."ves";
				} else if ($last2 === "fe") {
					return substr($name, 0, $len - 2)."ves";
				} else {
					// Shouldn't happen
					return null;
				}
			} else {
				$test2 = null;

				if ($last === "f") {
					$test2 = substr($name, $len - 3, 1);
				} else if ($last2 === "fe") {
					$test2 = substr($name, $len - 4, 1);
				}

				if (strpos(self::$vowels, $test) !== false && strpos(self::$vowels, $test2) === false) {
					if ($last === "f") {
						return substr($name, 0, $len - 1)."ves";
					} else if ($last2 === "fe") {
						return substr($name, 0, $len - 2)."ves";
					} else {
						// Should not happen
						return null;
					}
				} else {
					return $name."s";
				}
			}
		} else if ($last == "o") {
			switch ($name) {
				case "buffalo":
				case "domino":
				case "echo":
				case "embargo":
				case "hero":
				case "mosquito":
				case "potato":
				case "tomato":
				case "torpedo":
				case "veto": {
					return $name."es";
				}
				default: {
					return $name."s";
				}
			}
		} else if (in_array($last, self::$sibilants) || in_array($last2, self::$sibilants)) {
			switch ($name) {
				case "stomach":
				case "epoch": {
					return $name."s";
				}
				default: {
					return $name."es";
				}
			}
		} else {
			return $name."s";
		}
	}

	protected function __construct(int $id, $values = [], $db) {
		self::$db = $db;
		$this->id = $id;
		$this->values = $values;
	}

	public function __get($name) {
		if (isset($this->values[$name])) {
			return $this->values[$name];
		}
		
		self::$db->select($name, self::getPluralName())->where("id", $this->id)->query();
		$val = self::$db->getSingleValue($name);

		if (self::$db->getErrno()) {
		    if (in_array($name."_id", self::getProperties())) {
                self::$db->select($name."_id", self::getPluralName())->where("id", $this->id)->query();
                $res = self::$db->getSingleValue($name."_id");

                if (self::$db->getErrno()) {
                    throw new \Exception("No field matching $name found in model ".self::getName());
                } else {
                    $cName = ucwords($name);
                    // $cName = "\\hmo\\models\\".$cName;
                    return new $cName($res, [], self::$db);
                }
            } else {
                throw new \Exception("No field matching $name found in model ".self::getName());
            }
        } else {
		    $types = self::getPropertiesWithTypes();

		    if ($types[$name] == "datetime")
		        return new \DateTime($val);

		    if (strpos($types[$name], "tinyint") !== false)
		        return $val == 0 ? false : true;

		    return $val;
        }
    }

	public function __set($name, $value) {
        self::init_db();

	    if (is_object($value)) {
	        $end = null;

            if ($value instanceof \DateTime) {
                $end = $value->format("Y-m-d H:i:s");
            } else if ($value instanceof DynamicModel) {
                $end = $value->id;
            }

            self::$db->update(self::getPluralName())->set([$name => $end])->where("id", $this->id)->query();
        } else {
            if ($name == "id")
                return;

            $this->values[$name] = $value;

            self::$db->update(self::getPluralName())->set([$name => $value])->where("id", $this->id)->query();
            echo self::$db->getError();
        }
	}

	private static function getProperties(): array {
		self::init_db();

		self::$db->describe(self::getPluralName());
		$names = [];

		while ($row = self::$db->getRowArray()) {
			$names[] = strtolower($row["Field"]);
		}

		return $names;
	}

	private static function getPropertiesWithTypes(): array {
        self::init_db();

        self::$db->describe(self::getPluralName());
        $names = [];

        while ($row = self::$db->getRowArray()) {
            $names[strtolower($row["Field"])] = $row["Type"];
        }

        return $names;
    }

	public static function create(array $array): self {
		$allowed = self::getProperties();

		self::init_db();
		self::$db->insert(self::getPluralName());

		$columns = [];
		$values = [];

		foreach ($array as $property => $value) {
			if (!in_array($property, $allowed)) {
			    if (!in_array($property."_id", $allowed)) {
                    throw new \Exception("Attempted to set non-existent property $property on model ".get_called_class().".");
                } else {
			        $columns[] = $property."_id";
			        $values[] = $value;
			        continue;
                }
            }

			$columns[] = $property;
			$values[] = $value;
		}

		self::$db->columns(...$columns)->values(...$values)->query();

		if (self::$db->getErrno()) {
			throw new CampaignException("warning", "Couldn't instantiate model. MySQL said: ".self::$db->getError());
		}

		$name = get_called_class();
		return new $name(self::$db->getInsertId(), $array, self::$db);
	}

	public static function all(): array {
		self::init_db();
		$name = self::getPluralName();
		$called = get_called_class();
		$ret = [];

		self::$db->select(DBEngine::DB_ALL_COLUMNS, $name)->query();

		while ($row = self::$db->getRowArray()) {
			$ret[] = new $called($row["id"], $row, self::$db);
		}

		return $ret;
	}

	public static function findBy(string $column, $value): ?self {
	    $name = get_called_class();
        $columns = self::getProperties();

        if (!in_array($column, $columns))
            throw new \Exception("Attempted to find model '".self::getName()."' by non-existent column '$column'.");

        self::init_db();
        self::$db->select("id", self::getPluralName())->where($column, $value)->query();

        $val = self::$db->getSingleValue("id");
        return $val == null ? null : new $name($val, [], self::$db);
	}

    public static function find(int $id): self {
	    self::init_db();
        self::$db->select("id", self::getPluralName())->where("id", $id)->query();
        $name = get_called_class();

        $id = self::$db->getSingleValue("id");

        if ($id == null)
            throw new \Exception("Couldn't find any instance of ".self::getName()." with id $id.");

        return new $name($id, [], self::$db);
    }

    public static function take(): self {
	    self::init_db();
        self::$db->custom("SELECT `id` FROM ".self::getPluralName()." ORDER BY RAND() LIMIT 1");

        $id = self::$db->getSingleValue("id");

        if ($id == null)
            throw new \Exception("No records of ".self::getName()." are currently stored.");

        return self::find($id);
    }

    public static function first(int $amount = 1) {
        self::init_db();
        self::$db->select("id", self::getPluralName())->order("id", DBEngine::DB_ASC);

        if (self::$order != null) {
            self::$db->order(self::$order[0], DBEngine::DB_ASC);
            self::$order = null;
        }

        self::$db->limit($amount);
        $name = get_called_class();

        if ($amount = 1) {
            $id = self::$db->getSingleValue("id");

            if ($id == null)
                throw new \Exception("No records of " . self::getName() . " are currently stored.");

            return new $name($id, [], self::$db);
        } else {
            $ret = [];

            if (self::$db->getResult()->num_rows > 0) {
                while ($row = self::$db->getRowArray()) {
                    $ret[] = new $name($row['id'], [], self::$db);
                }

                return $ret;
            } else {
                throw new \Exception("No records of " . self::getName() . " are currently stored.");
            }
        }
    }

    public static function last(int $amount = 1) {
        self::init_db();
        self::$db->select("id", self::getPluralName())->order("id", DBEngine::DB_ASC);

        if (self::$order != null) {
            self::$db->order(self::$order[0], DBEngine::DB_DESC);
            self::$order = null;
        }

        self::$db->limit($amount);
        $name = get_called_class();

        if ($amount = 1) {
            $id = self::$db->getSingleValue("id");

            if ($id == null)
                throw new \Exception("No records of " . self::getName() . " are currently stored.");

            return new $name($id, [], self::$db);
        } else {
            $ret = [];

            if (self::$db->getResult()->num_rows > 0) {
                while ($row = self::$db->getRowArray()) {
                    $ret[] = new $name($row['id'], [], self::$db);
                }

                return $ret;
            } else {
                throw new \Exception("No records of " . self::getName() . " are currently stored.");
            }
        }
    }

    public static function order(string $column, int $order = null) {
        self::$order = [$column, $order];
    }

    public function update(array $values) {
	    $props = self::getProperties();

	    foreach ($values as $column => $value) {
	        if (!in_array($column, $props))
	            throw new \Exception("Attempted to update non-existent field $column on model ".self::getName());

	        $this->__set($column, $value);
        }
    }

    public static function updateAll(array $values) {
	    $props = self::getProperties();

	    foreach ($values as $column => $value)
	        if (!in_array($column, $props))
	            throw new \Exception("Attempted to update non-existent field $column on model ".self::getName());

	    self::init_db();
        self::$db->update(self::getName())->set($values);
    }

    public function destroy() {
	    self::$db->delete(self::getName())->where("id", $this->id);
    }

    public static function custom(string $query): ?array {
	    $name = get_called_class();
        self::init_db();

	    self::$db->custom($query);

	    $arr = [];

	    $res = self::$db->getResult();
	    /** @var mysqli_result $res */
        while ($row = $res->fetch_assoc()) {
            $arr[] = new $name($row["id"], $row, self::$db);
        }

        return $arr;
    }

    private static function init_db() {
		if (is_null(self::$db)) {
			self::$db = DB::getDBEngine();
		}
	}

}
