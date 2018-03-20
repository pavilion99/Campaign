<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("interactions.create", "gotv/events/list");

if (!isset($_GET["id"])) {
	header("Location: ../list");
	exit;
}

if (!empty($_POST)) {
	$sql = DB::get();

	$i = Interaction::find(intval($_POST["id"]));
	unset($_POST["id"]);
	$i->update($_POST);

	$_SESSION["message"]["content"] = "Event successfully updated!";
	$_SESSION["message"]["type"] = "success";
	header("Location: .");
	exit;
}

$event = Interaction::find(intval($_GET["id"]));
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
				<?php include("../../../assets/php/sidebar-gotv-events.php"); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Edit an Event</h1>
					<?php Campaign::message(); ?>
					<form action="" method="POST">
						<input type="hidden" name="id" value="<?= $_GET["id"] ?>">
						<input type="text" name="name" class="form-control" placeholder="Name" value="<?= $event->name ?>">
						<br />
						<select name="interaction_category_id" class="form-control">
							<?php
							$cats = [
								"2" => "Round Table",
								"3" => "Focus Group",
								"4" => "Meeting"
							];
							foreach ($cats as $i => $j): ?>
								<?php if(intval($event->interaction_category_id) == intval($i)): ?>
									<option value="<?= $i ?>" selected><?= $j ?></option>
								<?php else: ?>
									<option value="<?= $i ?>"><?= $j ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
						<br />
						<input type="datetime-local" name="date" placeholder="Date" class="form-control" value="<?= $event->date->format("Y-m-d\TH:i") ?>">
						<br />
						<textarea name="description" placeholder="Description (location...)" class="form-control"><?= $event->description ?></textarea>
						<br />
						<input type="submit" class="form-control btn btn-primary">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

