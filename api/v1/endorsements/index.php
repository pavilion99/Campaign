<?php
require_once("../../../assets/php/Campaign.php");

API::setup();

switch ($_SERVER["REQUEST_METHOD"]) {
	case "GET": {
		$endorsements = Endorsement::get_all();
		$endorse_objects = [];

		foreach ($endorsements as $endorsement) {
			$endorse_objects[] = API::make_api_object($endorsement);
		}

		exit(json_encode($endorse_objects));
	}
	case "POST": {
		break;
	}
}