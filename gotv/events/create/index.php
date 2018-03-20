<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("interactions.create", "gotv/events/list");

if (!empty($_POST)) {
	$sql = DB::get();

	$i = Interaction::create([
		"name" => $_POST["name"],
		"interaction_category_id" => $_POST["interaction_category_id"],
		"date" => $_POST["date"],
		"description" => $_POST["description"]
	]);

	$_SESSION["message"]["content"] = "Event successfully created!";
	$_SESSION["message"]["type"] = "success";
	header("Location: ../list");
	exit;
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
				<?php include("../../../assets/php/sidebar-gotv-events.php"); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Create an Event</h1>
					<?php Campaign::message(); ?>
					<form action="" method="POST">
						<input type="text" name="name" class="form-control" placeholder="Name">
						<br />
						<select name="interaction_category_id" class="form-control">
							<?php
							$cats = [
								"2" => "Round Table",
								"3" => "Focus Group",
								"4" => "Meeting"
							];
							foreach ($cats as $i => $j): ?>
								<option value="<?= $i ?>"><?= $j ?></option>
							<?php endforeach; ?>
						</select>
						<br />
						<input type="datetime-local" name="date" placeholder="Date" class="form-control">
						<br />
						<textarea name="description" placeholder="Description (location...)" class="form-control"></textarea>
						<br />
						<input type="submit" class="form-control btn btn-primary">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

