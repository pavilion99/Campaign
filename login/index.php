<?php
require_once("../assets/php/Campaign.php");
Campaign::setup();

if (isset($_SESSION["id"]) && $_SESSION["id"] > 0) {
	header("Location: ".Config::get("app-root"));
	exit;
}

if (!empty($_POST)) {
	$res = Campaign::authenticate($_POST["username"], $_POST["password"]);

	if (is_null($res)) {
		Campaign::msg("warning", "Incorrect username or password.");
		header("Location: .");
		exit;
	} else if($res instanceof User) {
		$_SESSION["id"] = $res->getId();
		header("Location: ".Config::get("app-root"));
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
	</head>
	<body class="login">
		<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
			<a class="navbar-brand" href="<?= Config::get("app-root"); ?>"><?= Config::get("campaign-name") ?></a>
		</nav>
		<div class="container-fluid h-100 d-flex align-items-center justify-content-center">
			<form action="" method="POST">
				<h1 class="mb-3 text-center">Login to Campaign</h1>
				<?php Campaign::message() ?>
				<input type="text" name="username" placeholder="Username" class="form-control mb-2">
				<input type="password" name="password" placeholder="Password" class="form-control mb-2">
				<div class="row">
					<div class="col-sm-6">
						<a class="btn btn-outline-secondary w-100" href="<?= Config::get("app-root") ?>reset">Reset Password</a>
					</div>
					<div class="col-sm-6">
						<input type="submit" value="Login" class="form-control btn btn-primary w-100">
					</div>
				</div>
			</form>
		</div>
	</body>
</html>
