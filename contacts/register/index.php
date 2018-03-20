<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("contacts.register", "contacts/list");

if (!empty($_POST)) {
	$contact = Contact::create([
		"first"         => $_POST["first"],
		"last"          => $_POST["last"],
		"residence"     => $_POST["residence"],
		"number"        => preg_replace("/^1/", "", preg_replace("/[^0-9]/","",$_POST["number"])),
		"email"         => strtolower($_POST["email"]),
		"year"          => intval($_POST["year"]),
		"best_contact"  => $_POST["best_contact"],
		"greek"         => $_POST["greek"],
		"school"        => $_POST["school"],
		"sl"			=> isset($_POST["sl"]) ? 1 : 0,
		"vip"			=> isset($_POST["vip"]) ? 1 : 0
	]);

	assert($contact instanceof Contact);
	$result = MailChimp::register_contact($contact);

	$contact->update(["mailchimp_id" => $result]);

	Campaign::msg("success", "Contact successfully registered!");
	header("Location: .");
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
					<h1 class="mb-4">Register a New Contact</h1>
					<?php Campaign::message(); ?>
					<h3 class="mb-3">Scrape Contact</h3>
					<form method="POST" action="<?= Config::get("app-root") ?>api/v1/directory/?redir=<?= Config::get("app-root") ?>contacts/register/">
						<input autofocus type="text" class="form-control mb-2" name="name" placeholder="Contact's First and Last Name">
						<input type="submit" class="btn btn-primary" value="Scrape Contact">
					</form>
					<h3 class="mb-3 mt-4">Scrape Contact</h3>
					<form method="POST" action="<?= Config::get("app-root") ?>api/v1/directory/?redir=<?= Config::get("app-root") ?>contacts/register/">
						<input type="email" class="form-control mb-2" name="email" placeholder="Contact's Email Address">
						<input type="submit" class="btn btn-primary" value="Scrape Contact">
					</form>
					<h3 class="mb-3 mt-4">Enter Data Manually</h3>
					<form action="" method="POST">
						<input type="text" name="first" placeholder="First Name" class="form-control">
						<br />
						<input type="text" name="last" placeholder="Last Name" class="form-control">
						<br />
						<textarea name="residence" class="form-control" placeholder="Residence"></textarea>
						<br />
						<input type="text" name="number" placeholder="Phone Number" class="form-control">
						<br />
						<input type="number" min="2014" max="2023" name="year" placeholder="Graduation Year" class="form-control">
						<br />
						<select name="school" class="form-control">
							<?php
							foreach ($schoolMap as $key => $value) {
								echo "<option value=\"$key\">$value</option>";
							}
							?>
						</select>
						<br />
						<select name="greek" class="form-control">
							<?php
							foreach ($greekMap as $key => $value) {
								echo "<option value=\"$key\">$value</option>";
							}
							?>
						</select>
						<br />
						<input type="text" class="form-control" name="best_contact" placeholder="Best Contact">
						<br />
						<input type="email" name="email" placeholder="Email Address" class="form-control">
						<br />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="student-leader" name="sl">
							<label class="form-check-label" for="student-leader">Student Leader</label>
						</div>
						<br />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="vip-leader" name="vip">
							<label class="form-check-label" for="vip-leader">VIP Leader</label>
						</div>
						<br />
						<input type="submit" class="btn btn-primary" value="Register Contact">
					</form>
				</div>
			</div>
		</div>
	</body>
</html>

