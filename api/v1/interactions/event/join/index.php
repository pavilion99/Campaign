<?php
require_once("../../../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
$has = Campaign::check_permission("interactions.create");

if (!$has)
	die(json_encode([
		"result" => "failure",
		"message" => "You do not have permission to perform this action."
	]));

if (!$_POST || empty($_POST))
	die(json_encode([
		"result" => "failure",
		"message" => "Something went wrong with the request."
	]));

$t = InteractionLink::custom("SELECT * FROM `interaction_links` WHERE `contact_id`=".$_POST["id"]." AND `interaction_id`=".$_POST["event"]);

if (!empty($t)) {
	$h = Campaign::check_permission("interactions.edit");

	if (!$h) {
		die(json_encode([
			"result" => "failure",
			"message" => "You do not have permission to perform this action."
		]));
	}

	$t = $t[0];
	$t->affiliation = intval($_POST["affiliation"]);
	exit(json_encode(["result" => "success"]));
}

$l = InteractionLink::create(["affiliation" => $_POST["affiliation"], "contact_id" => intval($_POST["id"]),"interaction_id" => intval($_POST["event"])]);

exit(json_encode(["result" => "success"]));