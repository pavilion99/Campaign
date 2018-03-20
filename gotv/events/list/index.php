<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("interactions.list", "");

$sql = DB::getDBEngine();

$interactions = Interaction::custom("SELECT * FROM `interactions` WHERE `interaction_category_id` IN (2,3,4)");

$idsArr = [];
foreach ($interactions as $interaction) {
	$idsArr[] = $interaction->id;
}

$ids = "(".implode(",", $idsArr).")";

if (empty($idsArr))
	$ids = "(0)";

$links = InteractionLink::custom("SELECT * FROM `interaction_links` INNER JOIN `interactions` ON `interaction_links`.`interaction_id` = `interactions`.`id` WHERE `interaction_id` IN $ids ORDER BY `interactions`.`date` DESC");
$cats = InteractionType::all();

$iMap = [];
$cMap = [];

$aMap = [
	0 => "",
	1 => "Strong Sky + Em",
	2 => "Lean Sky + Em",
	3 => "Undecided",
	4 => "Lean Opposition",
	5 => "Strong Opposition"
];

foreach ($interactions as $interaction) {
	$iMap[$interaction->id] = $interaction;
}

foreach ($cats as $cat) {
	$cMap[$cat->id] = $cat;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
		<script>
			function modalSet(id, name) {
				document.querySelector("#eventNameDeleteConfirm").innerHTML = name;
				document.querySelector("#idDeleteConfirm").href = "../delete/?id=" + id;
			}
		</script>
	</head>
	<body>
		<div class="modal fade" id="deleteConfirm" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="deleteConfirmLabel">Confirm</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure that you want to delete the event "<span id="eventNameDeleteConfirm"></span>"?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
						<a id="idDeleteConfirm" href="javascript:void(0);" class="btn btn-outline-danger">Delete Event</a>
					</div>
				</div>
			</div>
		</div>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php include("../../../assets/php/sidebar-gotv-events.php"); ?>
				<div class="col-lg-9 px-5 py-4">
					<div id="gotv-top">
						<h1 class="mb-4 d-inline-block">GOTV Events</h1>
					</div>
					<?php Campaign::message(); ?>
					<div class="scroll">
						<table class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Date</th>
									<th>Description</th>
									<th>Details</th>
									<?php if (Campaign::check_permission("interactions.edit")): ?>
										<th class="text-center">
											Edit
										</th>
									<?php endif;?>
									<?php if (Campaign::check_permission("interactions.delete")): ?>
										<th class="text-center">
											Delete
										</th>
									<?php endif;?>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($interactions)): ?>
									<tr>
										<td colspan="100">
											<b>No events found.</b>
										</td>
									</tr>
								<?php endif; ?>
								<?php foreach ($interactions as $event): ?>
									<tr>
										<td><?= $event->name ?></td>
										<td><?= $event->date ?></td>
										<td><?= $event->description ?></td>
										<td><a href="../details/?id=<?= $event->id ?>">Details</a></td>
										<?php if (Campaign::check_permission("interactions.edit")): ?>
											<td class="text-center">
												<a href="../edit/?id=<?= $event->id ?>"><span
															class="fa fa-pencil-square"></span></a>
											</td>
										<?php endif;?>
										<?php if (Campaign::check_permission("interactions.delete")): ?>
											<td class="text-center">
												<a data-toggle="modal" data-target="#deleteConfirm"
												   href="javascript:void(0);"
												   onclick="modalSet(<?= $event->id ?>,'<?= $event->name ?>');">
													<span class="fa fa-trash"></span>
												</a>
											</td>
										<?php endif;?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
