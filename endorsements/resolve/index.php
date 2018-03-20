<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("endorsements.resolve", "");

$id = $_GET["id"];
$resolution = $_GET["resolution"];

$e = Endorsement::get(intval($id));

if ($e == null) {
	Campaign::msg("warning", "Endorsement not found.");
	header("Location: ../pending");
	die;
}

$e->resolve($resolution);

Campaign::msg("success", "Endorsement successfully resolved.");

switch ($resolution) {
	case "accept": {
		header("Location: ../active");
		break;
	}
	case "ignore": {
		header("Location: ../pending");
		break;
	}
	default: {
		header("Location: ../pending");
		break;
	}
}
exit;