<?php
require_once("../../../assets/php/Campaign.php");

$var = json_decode($_POST["payload"], true);

if (!$var || !is_array($var) || $var["token"] != Config::get("slack-verification-token"))
	die("Hmm...it doesn't seem like this request came from Slack. Is the token correct?");

$arr = explode("_", $var["callback_id"]);

$type = $arr[0];
$id = intval($arr[1]);

switch ($type) {
	case "endorsement": {
		$e = Endorsement::get($id);
		if ($e->isActive() || $e->isIgnored())
			die("Sorry! It seems like that endorsement has already been resolved.");

		$action = $var["actions"][0]["value"];
		$e->resolve($action);

		exit("Okay! I've ".$action."d that endorsement.");
		break;
	}
	default: {
		die("Sorry! I couldn't understand that request.");
	}
}



