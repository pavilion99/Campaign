<?php
header("Location: ../../gotv/people/list");
die;

require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("contacts.list", "");

$sql = DB::get();

$contacts = [];

$contactsQL = $sql->query("SELECT * FROM `contacts` ORDER BY `id` ASC");
if (!$contactsQL) {
	$_SESSION["message"]["content"] = "Something went wrong with the database query. MySQL said: ".$sql->error.". Contact your Tech Chair with this information.";
	$_SESSION["message"]["type"] = "warning";
} else if ($row = $contactsQL->fetch_assoc()) {
	do {
		$contacts[] = $row;
	} while($row = $contactsQL->fetch_assoc());
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
					<h1 class="mb-4">All Contacts</h1>
					<?php Campaign::message(); ?>
					<div class="table-responsive">
						<table class="table">
							<tr>
								<th>
									Name
								</th>
								<th>
									Phone Number
								</th>
								<th>
									Email
								</th>
								<?php if (Campaign::check_permission("contacts.edit")): ?>
								<th class="text-center">
									Edit
								</th>
								<?php endif;?>
								<?php if (Campaign::check_permission("contacts.delete")): ?>
								<th class="text-center">
									Delete
								</th>
								<?php endif; ?>
							</tr>
							<?php if (empty($contacts)): ?>
							<tr>
								<td colspan="100">
									<b>No contacts found.</b>
								</td>
							</tr>
							<?php endif; ?>
							<?php foreach($contacts as $contact): ?>
							<tr>
								<td><?= $contact["first"]." ".$contact["last"]; ?></td>
								<td><?= $contact["number"] != null ? "+1 (".substr($contact["number"],0,3).") ".substr($contact["number"], 3, 3)."-".substr($contact["number"],6,4) : "<span class='text-muted'>None</span>" ?></td>
								<td><a href="mailto:<?= $contact["email"] ?>"><?= $contact["email"]; ?></a></td>
								<?php if (Campaign::check_permission("contacts.edit")): ?>
								<td class="text-center">
									<a href="../edit/?id=<?= $contact["id"] ?>"><span class="fa fa-pencil-square"></span></a>
								</td>
								<?php endif; ?>
								<?php if (Campaign::check_permission("contacts.delete")): ?>
								<td class="text-center">
									<a href="../delete/?id=<?= $contact["id"] ?>"><span class="fa fa-trash"></span></a>
								</td>
								<?php endif; ?>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
