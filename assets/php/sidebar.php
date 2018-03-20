<?php
require_once(__DIR__."/Config.php");
$activeURL = explode('/', $_SERVER["REQUEST_URI"]);


$activeSidebar = $activeURL[sizeof($activeURL) - 3];
include("sidebar-".$activeSidebar.".php");
