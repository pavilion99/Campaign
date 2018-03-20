<?php
require_once("Permission.php");
require_once("Config.php");
require_once("DB.php");
require_once("Endorsement.php");
require_once("User.php");
require_once("Addon.php");
require_once("Alert.php");
require_once("ExceptionCode.php");
require_once("Slack.php");
require_once("CampaignException.php");
require_once("DynamicModel.php");
require_once("DBEngine.php");
require_once("MySQL.php");
require_once("Interaction.php");
require_once("Contact.php");
require_once("InteractionLink.php");
require_once("InteractionType.php");
require_once("greek.php");
require_once("ResetLink.php");
require_once("CampaignMail.php");
require_once("Twilio.php");
require_once("MailChimp.php");

class Campaign {
	public static function check_active() {
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();

		if (!isset($_SESSION["id"]) || $_SESSION["id"] <= 0) {
			if ($_SERVER["REQUEST_URI"] != "/") {
				$_SESSION["message"]["content"] = "You must be logged in to perform that action.";
				$_SESSION["message"]["type"] = "info";
			}
			header("Location: ".Config::get("app-root")."login");
			die();
		}
	}

	public static function check_permission(string $name): bool {
		return Permission::has_permission($name);
	}

	public static function check_permission_redir(string $name, string $return) {
		$return = Config::get("app-root").$return;

		if(!self::check_permission($name)) {
			$_SESSION["message"]["content"] = "You do not have permission to do that. If you think you should, contact a campaign administrator requesting the following permission: ".$name.".";
			$_SESSION["message"]["type"] = "info";
			header("Location: ".$return);
			die();
		}
	}

	public static function setup() {
		if (session_status() != PHP_SESSION_ACTIVE)
			session_start();
	
		set_exception_handler("Campaign::exception_handler");
		register_shutdown_function("DB::close_all");
	}
	
	public static function exception_handler($e) {
		if (php_sapi_name() == "cli") {
			echo $e->getMessage();
			echo "\r\n";
			echo $e->getTraceAsString();
			
			return;
		}
		if ($e instanceof CampaignException) {
			self::msg($e->isFatal() ? "danger" : "warning", $e->getMessage());
			
			if ($e->isFatal()) {
				$_SESSION["error"]["message"] = $e->getMessage();
				$_SESSION["error"]["trace"] = $e->getTraceAsString();
				$_SESSION["error"]["redirect"] = $_SERVER["REQUEST_URI"];
				if (!headers_sent())
				header("Location: ".Config::get("app-root")."error");
				else
				echo '<script>window.addEventListener("load", function() {window.location = "'.Config::get("app-root").'error";});</script>';
			die;
			} else {
				header("Location: ".Config::get("app-root").$e->getRedirect());
				die;
			}
		} else {
			$_SESSION["error"]["trace"] = $e->getTraceAsString();
			$_SESSION["error"]["message"] = $e->getMessage();
			$_SESSION["error"]["redirect"] = $_SERVER["REQUEST_URI"];
			if (!headers_sent())
				header("Location: ".Config::get("app-root")."error");
			else
				echo '<script>window.location = "'.Config::get("app-root").'error";</script>';
			die;
		}
	}

	public static function head() {
		include("head.php");
	}

	public static function nav() {
		include("nav.php");
	}

	public static function sidebar() {
		include("sidebar.php");
	}

	public static function message() {
		include("message.php");
	}

	public static function msg(string $type, string $message) {
		$_SESSION["message"]["type"] = $type;
		$_SESSION["message"]["content"] = $message;
	}
	
	public static function authenticate(string $username, string $password): ?User {
		$sql = DB::get();

		$username = $sql->escape_string($username);
		$password = $sql->escape_string(md5($password));
		
		$res = $sql->query("SELECT * FROM `users` WHERE `username`='$username' AND `password`='$password'");
		
		if ($sql->errno) {
			throw new CampaignException("Error authenticating with database. MySQL said: ".$sql->error, "login");
		}
		
		if ($res->num_rows == 1) {
			return User::get($res->fetch_assoc()["id"]);
		} else {
			return null;
		}
	}
}
