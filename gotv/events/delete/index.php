<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("interactions.delete", "gotv/events/list");

if (!isset($_GET["id"])) {
	$_SESSION["message"]["content"] = "Malformed request.";
	$_SESSION["message"]["type"] = "info";
	header("Location: ../list");
	exit;
}

$id = $_GET["id"];

$sql = DB::get();

$sql->query("DELETE FROM `interactions` WHERE `id`=$id");

if ($sql->errno) {
	$_SESSION["message"]["content"] = "Error deleting event. MySQL said: ".$sql->error.". Contact the Tech Chair with this information.";
	$_SESSION["message"]["type"] = "warning";
	header("Location: ../list");
	die;
}

$_SESSION["message"]["content"] = "Event deleted successfully.";
$_SESSION["message"]["type"] = "success";
header("Location: ../list");