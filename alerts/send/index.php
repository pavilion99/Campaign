<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();
Campaign::check_active();
Campaign::check_permission_redir("alerts.send", "alerts/history");

require_once("../../assets/lib/Twilio/autoload.php");

use Twilio\Rest\Client;

if (!empty($_POST)) {
	die();

	$sql = DB::get();

	if (isset($_POST["send_now"])) {
		$result = Twilio::send($_POST["message]"], Contact::all());

		$i = Interaction::create(["interaction_category_id" => "5", "description" => $_POST["message"]]);

		$errors = $result["errors"];
		$sent = $result["sent"];

		$failed = [];
		foreach ($sent as $contact) {
			if (!array_key_exists($contact->id, $errors)) {
				$l = InteractionLink::create(["interaction_id" => $i->id, "affiliation" => "0", "contact_id" => $contact->id]);
			} else {
				$failed[] = $contact;
			}
		}
		$failedStr = implode(", ", $failed);
	}

	$res = $sql->query("SELECT * FROM `users` WHERE `id`=".$_SESSION["id"]);

	if (!$res || $res->num_rows == 0) {
		Campaign::msg("danger","FATAL: Couldn't find your username in the database. Something has gone terribly wrong. Contact the Tech Chair with this information." );
		header("Location: .");
		die();
	}

	$r = $res->fetch_assoc();
	$name = $r["firstname"]." ".$r["lastname"];

	$content = $sql->escape_string($_POST["message"]);
	$title = $sql->escape_string($_POST["title"]);

	if ($title != "")
		$sql->query("INSERT INTO `alerts` (`title`,`content`,`sent_by`) VALUES ('$title', '$content','$name')");
	else
		$sql->query("INSERT INTO `alerts` (`content`,`sent_by`) VALUES ('$content','$name')");

	if ($sql->errno) {
		$_SESSION["message"]["content"] = "Couldn't insert the alert into the database due to a malformed query. Contact the Tech Chair with this information.";
		$_SESSION["message"]["type"] = "warning";
		header("Location: .");
		die();
	}

	if (!$sql->insert_id) {
		$_SESSION["message"]["content"] = "The alert was created and sent, but we couldn't log it in the database. It will look like it didn't send.";
		$_SESSION["message"]["type"] = "warning";
		header("Location: .");
		die();
	}

	if (!empty($failed)) {
		Campaign::msg("warning", "Message failed to send to the following contacts: $failedStr");
	} else {
		$_SESSION["message"]["content"] = "Message sent successfully! Check your phone!";
		$_SESSION["message"]["type"] = "success";
	}

	header("Location: .");
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
		<script>
			function updateCount() {
			    let len = document.querySelector("#alert-content").value.length;
			    document.querySelector("#count").innerHTML = len;

			    if (len > 160) {
			        document.querySelector("#counter").classList.add("text-danger");
				} else {
                    document.querySelector("#counter").classList.remove("text-danger");
				}
			}
		</script>
	</head>
	<body>
		<?php Campaign::nav(); ?>
		<div class="container-fluid h-100">
			<div class="row" id="main-content">
				<?php Campaign::sidebar(); ?>
				<div class="col-lg-9 px-5 py-4">
					<h1 class="mb-4">Send a Text Alert</h1>
					<?php Campaign::message(); ?>
					<?php if (isset($_SESSION["alerts"]["send"]["error"])): ?>
						<?php if ($_SESSION["alerts"]["send"]["error"] != 0): ?>
							<div class="alert alert-danger">Message failed to send. Error code: <?php echo $_SESSION["alerts"]["send"]["error"]; ?></div>
							<?php unset($_SESSION["alerts"]["send"]["error"]); ?>
						<?php else: unset($_SESSION["alerts"]["send"]["error"]); ?>
							<div class="alert alert-success">Message sent successfully!</div>
						<?php endif; ?>
					<?php endif;?>
					<form action="" method="POST">
						<input type="text" class="form-control" name="title" placeholder="Title (optional">
						<br />
						<textarea id="alert-content" class="form-control" name="message" placeholder="Type message here..." maxlength="306" oninput="updateCount()"></textarea>
						<div id="counter" class="text-right text-secondary">
							<span id="count">0</span>/160
						</div>
						<br />
						<button type="submit" class="btn btn-default">Send Message</button>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>
