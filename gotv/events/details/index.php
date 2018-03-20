<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("interactions.list", "");

$sql = DB::getDBEngine();

if (!isset($_GET["id"])) {
	header("Location: ../list");
	exit;
}

$event = Interaction::find(intval($_GET["id"]));

$links = InteractionLink::custom("SELECT * FROM `interaction_links` WHERE `interaction_id`=".$event->id);
$contactIds = [];

foreach ($links as $link) {
	$contactIds[] = $link->contact_id;
}

$contacts = [];
foreach ($contactIds as $contactId) {
	$contacts[] = Contact::findBy("id",intval($contactId));
}

$cMap = [];
foreach ($contacts as $contact) {
	$cMap[$contact->id] = $contact;
}

$aMap = [
	0 => "",
	1 => "Strong Sky + Em",
	2 => "Lean Sky + Em",
	3 => "Undecided",
	4 => "Lean Opposition",
	5 => "Strong Opposition"
];
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
					<?php Campaign::message(); ?>
					<h1 class="mb-5">GOTV Event Details</h1>
					<table class="table">
						<tr>
							<td><b>Event Name</b></td>
							<td><?= $event->name ?></td>
						</tr>
						<tr>
							<td><b>Event Type</b></td>
							<td><?= InteractionType::find($event->interaction_category_id)->name ?></td>
						</tr>
						<tr>
							<td><b>Event Date</b></td>
							<td><?= $event->date->format("Y-m-d H:i:s") ?></td>
						</tr>
						<tr>
							<td><b>Event Description</b></td>
							<td><?= $event->description ?></td>
						</tr>
					</table>
					<h3>Event Attendees</h3>
					<table class="table">
						<thead>
							<tr>
								<th>Contact Name</th>
								<th>Affiliation</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($links)): ?>
								<tr><td colspan="3"><i>No attendees found. Add them from GOTV Contact Search.</i></td></tr>
							<?php endif; ?>
							<?php foreach($links as $link): ?>
							<tr>
								<td><?= $cMap[$link->contact_id]->first." ".$cMap[$link->contact_id]->last ?></td>
								<td><?= $aMap[$link->affiliation] ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</body>
</html>
