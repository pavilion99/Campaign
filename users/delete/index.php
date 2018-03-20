<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("users.delete", "users/list");

if (!isset($_GET["id"])) {
	header("Location: ../list");
	exit;
}

$id = $_GET["id"];

$sql = DB::get();

$sql->query("DELETE FROM `users` WHERE `id`=$id");

if ($sql->errno) {
	Campaign::msg("warning", "An error occurred: ".$sql->error);
} else {
	Campaign::msg("success", "User deleted successfully.");
}

header("Location: ../list");