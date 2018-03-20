<?php
require_once(__DIR__."/../assets/php/Config.php");
session_start();
session_destroy();

session_start();
$_SESSION["message"]["content"] = "Your session has ended.";
$_SESSION["message"]["type"] = "info";
header("Location: ".Config::get("app-root")."login");