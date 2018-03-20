<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once("../../assets/lib/PHPMailer/PHPMailer.php");
require_once("../../assets/lib/PHPMailer/POP3.php");
require_once("../../assets/lib/PHPMailer/Exception.php");
require_once("../../assets/lib/PHPMailer/OAuth.php");
require_once("../../assets/lib/PHPMailer/SMTP.php");

require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("users.create", "");

$sql = DB::get();

if (!empty($_POST)) {
	$username  = $sql->escape_string($_POST["username"]);
	$firstname = $sql->escape_string($_POST["firstname"]);
	$lastname  = $sql->escape_string($_POST["lastname"]);
	$position  = $sql->escape_string($_POST["position"]);
    $email     = $sql->escape_string($_POST['email']);

	$sql->query("INSERT INTO `users` (`username`,`firstname`,`lastname`,`password`,`position`,`email`) VALUES ('$username','$firstname','$lastname','','$position','$email')");

	if ($sql->errno) {
		$_SESSION["message"]["content"] = "Error during database query. MySQL said: ".$sql->error.". Contact your Tech Chair with this information.";
		$_SESSION["message"]["type"] = "warning";
		header("Location: ../list");
		die;
	}

	$rLink = uniqid();
	$reset = ResetLink::create(["link" => $rLink, "user" => $sql->insert_id]);

	$user = $sql->insert_id;
	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 5) != "perm-")
			continue;

		$perm = substr($key, 5, strlen($key));
		$perm = str_replace("_", ".", $perm);

		$sql->query("INSERT INTO `permissions` (`user`,`permission`,`value`) VALUES ($user,'$perm',1)");

		if($sql->errno) {
			$_SESSION["message"]["content"] = "Error during database query. MySQL said: ".$sql->error.". Contact your tech chair with this information.";
			$_SESSION["message"]["type"] = "warning";
			header("Location: ../list");
			die;
		}
	}

	$perms = Permission::get_known_permissions();
	foreach ($perms as $perm) {
		/** @var Permission $perm */
		$name = $perm->getName();
		if (!array_key_exists("perm-".str_replace(".","_",$name), $_POST)) {
			$sql->query("INSERT INTO `permissions` (`user`,`permission`,`value`) VALUES ($user,'$name',0)");
			if ($sql->errno) {
				$_SESSION["message"]["content"] = "Error during database query. MySQL said: ".$sql->error.". Contact your tech chair with this information.";
				$_SESSION["message"]["type"] = "warning";
				header("Location: ../list");
				die;
			}
		}
	}

	Campaign::msg("success", "User provisioned successfully.");
    header("Location: ../list");

	$body = "<p>
				        Welcome, $firstname!
				        <br><br>
				        We're so glad you're here. Thank you once again for joining our campaign as a student advocate. With your help, Sky + Emily for NU will be able to cover a lot of ground and get out the vote!
				        <br><br>
				        Your username for our campaign is <b>$username</b>. To set your password, please visit the following link: <a href=\"https://manage.skyandem.nu/reset/?link=$rLink\">https://manage.skyandem.nu/reset/?link=$rLink</a>.
				        <br ><br >
				        If you have any questions or run into any issues, please feel free to reply to this email or to send an email to < a href = \"mailto:gotv@skyandem.nu\" > gotv@skyandem.nu </a >.
				        <br ><br >
	In service,<br >
	Sky + Emily
    </p >";

    CampaignMail::send($body, "Welcome to Our Team!", $_POST["firstname"]." ".$_POST["lastname"], $_POST["email"], CampaignMail::ACCOUNT_GOTV);

	header("Location: ../list");
    die;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
		<script>
			function checkPassword(form) {
				return form.confirm.value === form.password.value;
			}
		</script>
	</head>
	<body>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Provision a New User</h1>
					<?php Campaign::message(); ?>
					<form method="POST" onsubmit="return checkPassword(this);">
						<h5 class="text-muted">User Details</h5>
						<input type="text" name="firstname" placeholder="First Name" class="form-control">
						<br />
						<input type="text" name="lastname" placeholder="Last Name" class="form-control">
						<br />
						<input type="text" name="username" placeholder="Username" class="form-control">
						<br />
						<input type="text" name="email" placeholder="Email Address" class="form-control">
						<br />
						<input type="text" name="position" placeholder="Position" class="form-control">
						<br />
						<h5 class="text-muted">Permissions</h5>
						<?php
						$columnsres = Permission::get_known_permissions();
						foreach($columnsres as $permission):
							/** @var Permission $permission */
							?>
							<input type="checkbox" name="perm-<?= $permission->getName() ?>" <?= $permission->getDefault() ? "checked" : "" ?> id="<?= $permission->getName() ?>" value="1">
							<label for="<?= $permission->getName() ?>"><?= $permission->getDescription() ?? $permission->getName() ?></label>
							<br />
						<?php endforeach; ?>
						<br />
						<input type="submit" class="btn btn-primary" value="Create User">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>
