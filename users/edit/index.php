<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("users.edit", "");

$sql = DB::get();

if (!empty($_POST)) {
	$id = $_POST["id"];

	$username = $sql->escape_string($_POST["username"]);
	$first = $sql->escape_string($_POST["firstname"]);
	$last = $sql->escape_string($_POST["lastname"]);
	$password = null;
	if ($_POST["password"] != "")
		$password = $sql->escape_string(md5($_POST["password"]));
	$position = $sql->escape_string($_POST["position"]);

	if ($password != null)
		$sql->query("UPDATE `users` SET `username`='$username',`firstname`='$first',`lastname`='$last',`password`='$password',`position`='$position' WHERE `id`=$id");
	else
		$sql->query("UPDATE `users` SET `username`='$username',`firstname`='$first',`lastname`='$last',`position`='$position' WHERE `id`=$id");

	$user = $id;
	$existingRes = $sql->query("SELECT * FROM `permissions` WHERE `user`=$user");

	foreach ($_POST as $key => $value) {
		if (substr($key, 0, 5) != "perm-")
			continue;
		$perm = substr($key, 5, strlen($key));
		$perm = str_replace("_", ".", $perm);

		$res = $sql->query("SELECT * FROM `permissions` WHERE `user`=$user AND `permission`='$perm'");
		if ($res->num_rows == 0)
			$sql->query("INSERT INTO `permissions` (`user`,`permission`,`value`) VALUES ($user,'$perm',1)");
		else
			$sql->query("UPDATE `permissions` SET `value`=1 WHERE `user`=$user AND `permission`='$perm'");

		if($sql->errno)
			die($sql->error);
	}

	if ($existingRes->num_rows != 0) {
		while ($row = $existingRes->fetch_assoc()) {
			if (!array_key_exists("perm-".str_replace(".", "_", $row["permission"]), $_POST)) {
				$perm = $row["permission"];
				$sql->query("UPDATE `permissions` SET `value`=0 WHERE `permission`='$perm' AND `user`=$user");

				if($sql->errno)
					die($sql->error);
			}
		}
	}

	Campaign::msg("success", "User edited successfully.");
	header("Location: ../list");
	exit;
}

$user = $sql->query("SELECT * FROM `users` WHERE `id`=".$_GET["id"]);

if ($sql->errno) {
	$_SESSION["message"]["content"] = "Error occured during database query. MySQL said: ".$sql->error.". Contact your Tech Chair with this information.";
	$_SESSION["message"]["type"] = "warning";
	header("Location: ../list");
	exit;
}

if ($user->num_rows == 0) {
	$_SESSION["message"]["content"] = "The specified user (".$_GET["id"].") does not seem to exist in the database. No action was taken. Contact your Tech Chair if you believe this to be in error.";
	$_SESSION["message"]["type"] = "warning";
	header("Location: ../list");
	exit;
} else {
	$user = $user->fetch_assoc();
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
					<h1 class="mb-4">Edit User "<?= $user["username"] ?>"</h1>
					<?php Campaign::message(); ?>
					<form method="POST" onsubmit="return checkPassword(this);">
						<h5 class="text-muted">User Details</h5>
						<input type="hidden" name="id" value="<?= $_GET["id"] ?>">
						<input type="text" name="firstname" placeholder="First Name" class="form-control" value="<?= $user["firstname"] ?>">
						<br />
						<input type="text" name="lastname" placeholder="Last Name" class="form-control" value="<?= $user["lastname"] ?>">
						<br />
						<input type="text" name="username" placeholder="Username" class="form-control" value="<?= $user["username"] ?>">
						<br />
						<input type="password" name="password" placeholder="Password" class="form-control">
						<br />
						<input type="password" name="confirm" placeholder="Confirm password" class="form-control">
						<br />
						<input type="text" name="position" placeholder="Position" class="form-control" value="<?= $user["position"] ?>">
						<br />
						<h5 class="text-muted">Permissions</h5>
						<?php
						$columnsres = Permission::get_known_permissions();
						foreach($columnsres as $permission):
								/** @var Permission $permission */
						?>
						<input type="checkbox" name="perm-<?= $permission->getName() ?>" <?= $permission->user_has_this_permission($_GET["id"]) ? "checked" : "" ?> id="<?= $permission->getName() ?>" value="1">
						<label for="<?= $permission->getName() ?>"><?= $permission->getDescription() ?? $permission->getName() ?></label>
						<br />
						<?php endforeach; ?>
						<br />
						<input type="submit" class="btn btn-primary" value="Save User">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>
