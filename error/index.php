<?php require_once("../assets/php/Campaign.php"); Campaign::setup();
if (!isset($_SESSION["error"]["message"] )) {
	header("Location: ".$_SESSION["error"]["redirect"]);
	die;
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
			<div>
				<h1 class="mb-3 text-center">An Error Has Occurred</h1>
				<?php Campaign::message() ?>
				<h3>
					Stack Trace
				</h3>
				<div class="card p-3">

					<code>
						<?= $_SESSION["error"]["message"]; ?>
						<br />
						<?= $_SESSION["error"]["trace"]; ?>
						<?php unset($_SESSION["error"]["message"]); unset($_SESSION["error"]["trace"]); ?>
					</code>
				</div>
				<br />
				<b>Report this information to the Tech Director.</b>
			</div>
		</div>
	</body>
</html>
