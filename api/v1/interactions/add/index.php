<?php
require_once("../../../../assets/php/Campaign.php");
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

$i = Interaction::create(["interaction_category_id" => $_POST["type"]]);
$l = InteractionLink::create(["affiliation" => $_POST["affiliation"], "contact_id" => $_POST["contact_id"],"interaction_id" => $i->id,"notes" => $_POST["notes"]]);

exit(json_encode(["result" => "success"]));