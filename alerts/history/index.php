<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("alerts.list", "");

$sql = DB::get();

$alerts = [];

$alertsQL = $sql->query("SELECT * FROM `alerts` ORDER BY `sent` DESC");

if ($sql->errno) {
	$_SESSION["message"]["content"] = "An error occurred while executing the MySQL query. Contact the Tech Chair with this information.";
	$_SESSION["message"]["type"] = "warning";
} else {
	if ($row = $alertsQL->fetch_assoc()) {
		do {
			$alerts[] = $row;
		} while ($row = $alertsQL->fetch_assoc());
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
	</head>
	<body>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Alert History</h1>
					<?php Campaign::message(); ?>
					<?php if (empty($alerts)): ?>
						<b>No alerts found.</b>
					<?php endif; ?>
					<?php foreach ($alerts as $alert): ?>
						<div class="card mb-3">
							<div class="card-body">
								<h5 class="card-title"><?= $alert["title"] ?></h5>
								<h6 class="card-subtitle mb-2"><span class="text-muted"><?= (new DateTime($alert["sent"]))->format("l, F j, Y g:ia"); ?></span><?php if($alert["sent_by"] != ""): ?> by <span class="text-muted"><?= $alert["sent_by"] ?></span><?php endif; ?></h6>
								<p class="card-text"><?= $alert["content"] ?></p>
								<form action="../send/" method="POST">
									<input type="hidden" name="message" value="<?= $alert["content"] ?>">
									<input type="hidden" name="title" value="<?= $alert["title"] ?>">
									<button type="submit" class="btn btn-link">Resend</button>
								</form>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</body>
</html>
