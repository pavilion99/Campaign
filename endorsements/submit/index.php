<?php
require_once("../../assets/php/Campaign.php");
Campaign::setup();

if ($_POST && !empty($_POST)) {
	$title = $_POST["title"];
	$content = $_POST["content"];
	$sender = $_POST["sender"];

	$e = Endorsement::create($title, $content, $sender);

	if (Config::get("enable-slack-integration") == "true")
		Slack::submit_endorsement($e);

	Campaign::msg("success", "Your endorsement was submitted! Check back soon to see your writing on our website.");
	header("Location: .");
	exit;
}
?>
<!-- This page to be themed later -->
<html>
	<head>
		<?php Campaign::head(); ?>
	</head>
	<body>
		<div class="container-fluid text-center">
			<h1>Endorse Sky + Emily</h1>
			<?php Campaign::message(); ?>
			<form method="POST" class="mr-auto ml-auto" style="width: 500px;">
				<input class="form-control mb-2" type="text" name="title" placeholder="Title">
				<textarea class="form-control mb-2" name="content" placeholder="Content"></textarea>
				<input class="form-control mb-2" type="text" name="sender" placeholder="Sender">
				<input class="form-control mb-2" type="submit" value="Submit">
			</form>
		</div>
	</body>
</html>