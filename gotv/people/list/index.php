<?php
require_once("../../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("contacts.list", "");

$sql = DB::getDBEngine();

$order  = $_GET["order"] ?? "id";
$dir    = $_GET["dir"] ?? "DESC";
$page   = $_GET["page"] ?? 1;
$shown  = $_GET["shown"] ?? 50;
$offset = (intval($page) - 1) * $shown;

$search = $_GET["search"] ?? "";
$search = "%".$search."%";

$sql->custom("SELECT count(*) AS 'count' FROM `contacts`");
$countHeader = $sql->getResult()->fetch_assoc()["count"];
$countHeader = intval($countHeader);

$searchId = intval($_GET["search"]);

$contacts = Contact::custom("SELECT * FROM `contacts` WHERE CONCAT(`first`, ' ', `last`) LIKE '$search' OR `first` LIKE '$search' OR `last` LIKE '$search' OR `email` LIKE '$search' OR `school` LIKE '$search' OR `greek` LIKE '$search' OR `id`=$searchId OR `residence` LIKE '$search' ORDER BY `$order` $dir LIMIT $shown OFFSET $offset");
$sql->custom("SELECT count(*) AS 'count' FROM `contacts` WHERE CONCAT(`first`, ' ', `last`) LIKE '$search' OR `first` LIKE '$search' OR `last` LIKE '$search' OR `email` LIKE '$search' OR `school` LIKE '$search' OR `greek` LIKE '$search' OR `id`=$searchId OR `residence` LIKE '$search' ORDER BY `$order` $dir");
$count = $sql->getResult()->fetch_assoc()["count"];
$count = intval($count);

$hasPerm = Campaign::check_permission("contacts.superuser");
$rc = $count > 20 ? 20 : $count;

$pages = ceil($count / $shown);

if ($page > $pages)
	$page = $pages;

$get_sort_name = $_GET;
$get_sort_name["dir"] = $dir == "DESC" ? "ASC" : "DESC";
$get_sort_name["order"] = "first";
$sort_name = http_build_query($get_sort_name);

$get_sort_last = $_GET;
$get_sort_last["dir"] = $dir == "DESC" ? "ASC" : "DESC";
$get_sort_last["order"] = "last";
$sort_last = http_build_query($get_sort_last);

$get_sort_year = $_GET;
$get_sort_year["dir"] = $dir == "DESC" ? "ASC" : "DESC";
$get_sort_year["order"] = "year";
$sort_year = http_build_query($get_sort_year);

$interactions = Interaction::all();
$links = InteractionLink::custom("SELECT * FROM `interaction_links` INNER JOIN `interactions` ON `interaction_links`.`interaction_id` = `interactions`.`id` ORDER BY `interactions`.`date` DESC");
$cats = InteractionType::all();

$events = Interaction::custom("SELECT * FROM `interactions` WHERE `interaction_category_id` IN (2,3,4) ORDER BY `date` DESC");

$schoolMap = [
	"WCAS" => "Weinberg",
	"MEAS" => "McCormick",
	"JOUR" => "Medill",
	"SPCH" => "Comm",
	"MUSI" => "Bienen",
	"SESP" => "SESP",
	"OTHER" => "Other",
	"UNK" => "Unknown"
];

$yearMap = [
	"2022" => "Entering",
	"2021" => "Freshman",
	"2020" => "Sophomore",
	"2019" => "Junior",
	"2018" => "Senior",
	"2017" => "Senior (2)"
];

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
			window.openInteractions = function(id) {
				document.getElementById("interactions-body").innerHTML = document.getElementById("contact-interactions-" + id).innerHTML;
				document.getElementById("interactions-contact-name").textContent = document.getElementById("contact-first-name-" + id).textContent + " " + document.querySelector("#contact-last-name-" + id).textContent;

				$("#interactionsDialog").modal('toggle');
			};

			window.makeAttendee = function() {
				let x = new XMLHttpRequest();
				x.open("POST", "<?= Config::get("app-root") ?>api/v1/interactions/event/join/", true);

				let data = new FormData();

				let form = document.forms.makeAttendee;
				let event = form.elements.event.value;
				let affiliation = form.elements.affiliation.value;
				let id = form.elements.id.value;

				data.append("event", event);
				data.append("id", id);
				data.append("affiliation", affiliation);

				x.onreadystatechange = function() {
					if (this.readyState === 4 && this.status === 200) {
						let res = JSON.parse(this.responseText);
						if (res.result === "success") {
							form.innerHTML = "<div class='alert alert-success'>Event joined successfully. <a href='javascript:window.location.href=window.location.href'>Back to contacts</a></div>";
						} else {
							form.innerHTML = "<div class='alert alert-warning'>Something went wrong. (" + res.message +") Try again. <a href='javascript:window.location.href=window.location.href'>Back to contacts</a></div>";
						}
					}
				};

				for (let i = 0; i < form.elements.length; i++) {
					form.elements[i].disabled = "disabled";
				}

				x.send(data);
			};
			
			window.addInteraction = function() {
				let x = new XMLHttpRequest();
				x.open("POST", "<?= Config::get("app-root") ?>api/v1/interactions/add/", true);

				let data = new FormData();

				let form = document.forms.addInteraction;
				let type = form.elements.type.value;
				let affiliation = form.elements.affiliation.value;
				let notes = form.elements.notes.value;

				data.append("type", type);
				data.append("affiliation", affiliation);
				data.append("notes", notes);

				let id = form.elements.contactId.value;

				data.append("contact_id", id);

				x.onreadystatechange = function() {
					if (this.readyState === 4 && this.status === 200) {
						let res = JSON.parse(this.responseText);
						if (res.result === "success") {
							form.innerHTML = "<div class='alert alert-success'>Interaction added successfully. <a href='javascript:window.location.href=window.location.href'>Back to contacts</a></div>";
						} else {
							form.innerHTML = "<div class='alert alert-warning'>Something went wrong. (" + res.message +") Try again. <a href='javascript:window.location.href=window.location.href'>Back to contacts</a></div>";
						}
					}
				};

				for (let i = 0; i < form.elements.length; i++) {
					form.elements[i].disabled = "disabled";
				}

				x.send(data);
			}
		</script>
	</head>
	<body>
		<div class="modal fade" id="interactionsDialog" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Interactions for <b id="interactions-contact-name"></b></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" id="interactions-body">

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<?php Campaign::nav(); ?>
		<div class="container-fluid">
			<div class="p-2 h-100" id="main-content">
				<div id="gotv-top" class="d-flex flex-row justify-content-between align-items-center">
					<h1 class="mb-4 d-inline-block">GOTV Data <span class="text-muted">(<?= $countHeader." Contacts, ".($hasPerm ? $count : $rc)." shown" ?>)</span></h1>
					<form class="d-inline-block text-right">
						<div class="input-group">
							<input type="text" class="form-control" name="search" placeholder="Quick Search">
							<div class="input-group-append">
								<button class="btn btn-outline-primary" type="submit"><span class="fa fa-search"></span></button>
							</div>
						</div>
						<a class="btn btn-sm btn-link" href="../search">Advanced Search</a>
					</form>
				</div>
				<?php Campaign::message(); ?>
				<div class="scroll">
					<div class="gotv-table-container">
						<table class="table gotv-table">
							<thead>
								<tr>
									<th>Flags</th>
									<th>
										<a href="?<?= $sort_name ?>">First Name <?= $order == "first" ? '<span class="fa fa-'.($dir == "ASC" ? "sort-up" : "sort-down").'"></span>' : ""; ?></a>
									</th>
									<th>
										<a href="?<?= $sort_last ?>">Last Name <?= $order == "last" ? '<span class="fa fa-'.($dir == "ASC" ? "sort-up" : "sort-down").'"></span>' : ""; ?></a>
									</th>
									<th>
										School
									</th>
									<th>
										<a href="?<?= $sort_year ?>">Year <?= $order == "year" ? '<span class="fa fa-'.($dir == "ASC" ? "sort-down" : "sort-up").'"></span>' : ""; ?></a>
									</th>
									<th>
										Greek Affiliation
									</th>
									<th>
										Best Contact
									</th>
									<th>
										Email
									</th>
									<th>
										Interactions
									</th>
									<?php if (Campaign::check_permission("contacts.edit")): ?>
										<th class="text-center">
											Edit
										</th>
									<?php endif;?>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($contacts)): ?>
									<tr>
										<td colspan="100">
											<b>No contacts found. <a href="../../../contacts/register">Add a new contact.</a></b>
										</td>
									</tr>
								<?php endif; ?>
								<?php $ct = 0; ?>
								<?php foreach($contacts as $contact): ?>
									<?php $ct++ ?>
									<?php if (!$hasPerm && $ct > 20) break; ?>
									<tr>
										<td>
											<?= $contact->sl == 1 ? "<span class=\"badge badge-pill badge-secondary\">SL</span>" : "" ?>
											<?= $contact->vip == 1 ? "<span class=\"badge badge-pill badge-secondary\">VIP</span>" : "" ?>
										</td>
										<td>
											<span id="contact-first-name-<?= $contact->id ?>"><?= $contact->first ?></span>
										</td>
										<td>
											<span id="contact-last-name-<?= $contact->id ?>"><?= $contact->last ?></span>
										</td>
										<td><?= $schoolMap[$contact->school] ?></td>
										<td><?= $yearMap["$contact->year"] ?></td>
										<td><?= $greekMap[$contact->greek] ?></td>
										<td><?= $contact->best_contact ?></td>
										<td><a href="<?= $contact->email != null && $contact->email != "" ? "mailto:".$contact->email : "" ?>"><?= $contact->email != null && $contact->email != "" ? $contact->email : "<span class='text-muted'>No email.</span>" ?></a></td>
										<td>
											<a href="javascript:openInteractions(<?= $contact->id ?>)">Interactions</a>
											<div class="d-none" id="contact-interactions-<?= $contact->id ?>">
												<h4>New Interaction</h4>
												<form method="POST" action="javascript:void(0);" onsubmit="window.addInteraction()" name="addInteraction">
													<input type="hidden" name="action" value="add_interaction">
													<input type="hidden" name="contactId" value="<?= $contact->id ?>">
													<select class="form-control mb-2" name="type" id="interaction-type">
														<option value="1">One-on-One</option>
														<option value="5">Text</option>
														<option value="6">Committed</option>
														<option value="7">Voted</option>
													</select>
													<div class="text-center mb-2">
														<div class="form-check form-check-inline">
															<input type="radio" name="affiliation" value="1" id="strong-us" class="form-check-input">
															<label class="form-check-label" for="strong-us">Strong Sky + Em</label>
														</div>
														<div class="form-check form-check-inline">
															<input type="radio" name="affiliation" value="2" id="lean-us" class="form-check-input">
															<label class="form-check-label" for="lean-us">Lean Sky + Em</label>
														</div>
														<div class="form-check form-check-inline">
															<input type="radio" name="affiliation" value="3" id="undecided" class="form-check-input">
															<label class="form-check-label" for="undecided">Undecided</label>
														</div>
														<div class="form-check form-check-inline">
															<input type="radio" name="affiliation" value="4" id="lean-opp" class="form-check-input">
															<label class="form-check-label" for="lean-opp">Lean Opposition</label>
														</div>
														<div class="form-check form-check-inline">
															<input type="radio" name="affiliation" value="5" id="strong-opp" class="form-check-input">
															<label class="form-check-label" for="strong-opp">Strong Opposition</label>
														</div>
													</div>
													<textarea name="notes" class="form-control mb-2" placeholder="Notes"></textarea>
													<input type="submit" class="btn btn-primary mb-3">
												</form>
												<br />
												<form name="makeAttendee" class="form-inline justify-content-between" action="javascript:void(0);" onsubmit="window.makeAttendee()">
													<input type="hidden" name="id" value="<?= $contact->id ?>">
													<select class="form-control" name="event">
														<?php foreach ($events as $event): ?>
														<option value="<?= $event->id ?>"><?= $event->name ?></option>
														<?php endforeach; ?>
													</select>
													<div id="event-affiliation">
														<div class="form-check">
															<input type="radio" name="affiliation" value="1" id="strong-us" class="form-check-input">
															<label class="form-check-label" for="strong-us">Strong Sky + Em</label>
														</div>
														<div class="form-check">
															<input type="radio" name="affiliation" value="2" id="lean-us" class="form-check-input">
															<label class="form-check-label" for="lean-us">Lean Sky + Em</label>
														</div>
														<div class="form-check">
															<input type="radio" name="affiliation" value="3" id="undecided" class="form-check-input">
															<label class="form-check-label" for="undecided">Undecided</label>
														</div>
														<div class="form-check">
															<input type="radio" name="affiliation" value="4" id="lean-opp" class="form-check-input">
															<label class="form-check-label" for="lean-opp">Lean Opposition</label>
														</div>
														<div class="form-check">
															<input type="radio" name="affiliation" value="5" id="strong-opp" class="form-check-input">
															<label class="form-check-label" for="strong-opp">Strong Opposition</label>
														</div>
													</div>
													<input type="submit" class="form-control btn btn-primary" value="Make Contact Attendee">
												</form>
												<br>
												<?php

													$filteredInteractions = [];

													foreach ($links as $interaction) {
														if ($interaction->contact_id != $contact->id)
															continue;
														
														$filteredInteractions[] = $interaction;
													}
												
													if (empty($filteredInteractions)):
														echo "<b>No known interactions with $contact->first $contact->last.</b>";
													else:
												?>
													<div class="table-responsive">
												<table class="table">
													<tr>
														<th>Interaction Type</th>
														<th>Name</th>
														<th>Affiliation</th>
														<th>Date</th>
														<th>Notes</th>
													</tr>

													<?php
													foreach ($filteredInteractions as $interaction):
														?>
													<tr>
														<td><?= $cMap[$iMap[$interaction->interaction_id]->interaction_category_id]->name ?></td>
														<?php if(in_array($iMap[$interaction->interaction_id]->interaction_category_id, [2, 3, 4])): ?>
														<td><a href="../../events/details/?id=<?= $iMap[$interaction->interaction_id]->id ?>"><?= $iMap[$interaction->interaction_id]->name ?></a></td>
														<?php else: ?>
														<td></td>
														<?php endif; ?>
														<td><?= $aMap[$interaction->affiliation] ?></td>
														<td><?= $iMap[$interaction->interaction_id]->date ?></td>
														<td><?= $interaction->notes ?></td>
													</tr>
													<?php endforeach; ?>
												</table>
													</div>
												<?php endif; ?>
											</div>
										</td>
										<?php if (Campaign::check_permission("contacts.edit")): ?>
											<td class="text-center">
												<a href="../../../contacts/edit/?id=<?= $contact->id ?>"><span class="fa fa-pencil-square"></span></a>
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
								<?php if (!$hasPerm && $ct < $count): ?>
									<tr>
										<td colspan="10" class="text-center"><b>Your search has been limited to 20 contacts, because you are not a superuser. Make your search more specific.</b></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php if ($hasPerm): ?>
				<div class="text-center">
					<div class="pagination text-center d-inline-block">
							<?php
							$last = $pages;

							$displayed = [];
							for ($i = $page - 2; $i <= $page + 2; $i++) {
								if ($i < 1 || $i > $pages)
									continue;

								$displayed[] = $i;
							}

							$queries = [];
							$query_first = $_GET;
							$query_first["page"] = "1";
							$queries["first"] = http_build_query($query_first);

							$query_last = $_GET;
							$query_last["page"] = "$last";
							$queries["last"] = http_build_query($query_last);

							if (!in_array(1, $displayed)) {
								echo "<a class='btn btn-default' href='?".$queries["first"]."'><<</a>";
							}

							foreach($displayed as $display) {
								$query_exs = $_GET;
								$query_exs["page"] = $display;
								$q = http_build_query($query_exs);
								
								if ($page == $display)
									$display = "<b>".$display."</b>";
								
								echo "<a class='btn btn-default' href='?".$q."'>".$display."</a>";
							}

							if (!in_array($last, $displayed)) {
								echo "<a class='btn btn-default' href='?".$queries["last"]."'>>></a>";
							}
							?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</body>
</html>
