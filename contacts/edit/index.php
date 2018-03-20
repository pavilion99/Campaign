<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("contacts.edit", "contacts/list");

if (!empty($_POST)) {
	$number = "";
	if ($_POST["number"] == "")
		$number = null;
	else {
		$number = preg_replace("/[^0-9]/","", preg_replace("/^1/", "", $_POST["number"]));
		$number = $number == "" ? "NULL" : $number;
	}

	$contact = Contact::find(intval($_POST["id"]));

	if (!$contact) {
		Campaign::msg("warning","Couldn't find that contact (".$_POST["id"].") in the database.");
		header("Location: ../list");
		die;
	}

	$contact->update([
		"first"         => $_POST["first"],
		"last"          => $_POST["last"],
		"residence"     => $_POST["residence"],
		"greek"         => $_POST["greek"],
		"best_contact"  => $_POST["best_contact"],
		"year"          => $_POST["year"],
		"email"         => strtolower($_POST["email"]),
		"number"        => $number,
		"sl"			=> isset($_POST["sl"]) ? 1 : 0,
		"vip"			=> isset($_POST["vip"]) ? 1 : 0
	]);

	assert($contact instanceof Contact);
	MailChimp::update_contact($contact);

	Campaign::msg("success", "User edited successfully.");
	header("Location: ../list");
	exit;
}

$contact = Contact::find(intval($_GET["id"]));

if (!$contact) {
	Campaign::msg("warning", "The action could not be completed due to a malformed query. Contact the Tech Chair with this information.");
	header("Location: ../list");
	die();
}

if (!isset($_GET["id"])) {
	Campaign::msg("info", "Malformed request.");
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
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Edit Existing Contact</h1>
					<?php Campaign::message(); ?>
					<form method="POST">
						<input type="hidden" name="id" value="<?= $_GET["id"] ?>">
						<input type="text" name="first" placeholder="First Name" class="form-control" value="<?= $contact->first ?>">
						<br />
						<input type="text" name="last" placeholder="Last Name" class="form-control" value="<?= $contact->last ?>">
						<br />
						<textarea name="residence" class="form-control" placeholder="Residence"><?= $contact->residence ?></textarea>
						<br />
						<input type="text" name="number" placeholder="Phone Number" class="form-control" value="<?= $contact->number ?>">
						<br />
						<input type="text" name="year" placeholder="Year" class="form-control" value="<?= $contact->year ?>">
						<br />
						<select name="greek" class="form-control">
							<?php
							foreach ($greekMap as $key => $value) {
								if ($contact->greek == $key)
									echo "<option value=\"$key\" selected>$value</option>";
								else
									echo "<option value=\"$key\">$value</option>";
							}
							?>
						</select>
						<br />
						<input type="text" class="form-control" name="best_contact" placeholder="Best Contact" value="<?= $contact->best_contact ?>">
						<br />
						<input type="email" name="email" placeholder="Email Address" class="form-control" value="<?= $contact->email ?>">
						<br />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="student-leader" name="sl"<?= $contact->sl == 1 ? " checked" : "" ?>>
							<label class="form-check-label" for="student-leader">Student Leader</label>
						</div>
						<br />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="vip-leader" name="vip"<?= $contact->vip == 1 ? " checked" : "" ?>>
							<label class="form-check-label" for="vip-leader">VIP Leader</label>
						</div>
						<br />
						<input type="submit" class="btn btn-primary" value="Edit Contact">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

