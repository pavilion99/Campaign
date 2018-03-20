<?php
require_once("../assets/lib/PHPMailer/PHPMailer.php");
require_once("../assets/lib/PHPMailer/POP3.php");
require_once("../assets/lib/PHPMailer/SMTP.php");
require_once("../assets/lib/PHPMailer/OAuth.php");
require_once("../assets/lib/PHPMailer/Exception.php");
require_once("../assets/php/Campaign.php");
Campaign::setup();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION["id"]) && $_SESSION["id"] > 0) {
	header("Location: ".Config::get("app-root"));
	exit;
}

if (!empty($_POST)) {
	switch ($_POST["action"]) {
		case ("send"): {
			$db = DB::getDBEngine();
			$db->select(DBEngine::DB_ALL_COLUMNS, "users")->where("email", strtolower($_POST["email"]))->query();
			$user = $db->getArray();

			if (empty($user)) {
				Campaign::msg("warning", "No user found with that email address.");
				header("Location: .");
				die();
			} else {
				$user = $user[0];
				$name = $user["first"]." ".$user["last"];
				$username = $user["username"];

				$link = uniqid();
				ResetLink::create(["user" => $user["id"], "link" => $link]);

				$body = "<p>
				        You are receiving this email because a password reset request was issued for your account on our
				        campaign site.
				        <br><br>
				        Your username is $username. To reset your password, please use the following link:
				        <a href=\"https://manage.skyandem.nu/reset/?link=$link\">https://manage.skyandem.nu/reset/?link=$link</a>.
				        <br><br>
				        If you did not request this change, you can safely disregard this email.
				        <br><br>
				        In service,<br>
				        Sky + Emily
				    </p>";

				CampaignMail::send($body, "Sky + Emily Password Reset", $name, $user["email"], CampaignMail::ACCOUNT_ACCOUNTS);

				try {
					$mail = new PHPMailer();
					$mail->isSMTP();                            // Set mailer to use SMTP
					$mail->Host = 'sub5.mail.dreamhost.com';    // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                     // Enable SMTP authentication
					$mail->Username = Config::get("gotv-email-username");
					$mail->Password = Config::get("gotv-email-password");
					$mail->SMTPSecure = 'tls';                  // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 587;                          // TCP port to connect to

					//Recipients
					$mail->setFrom('gotv@skyandem.nu', 'Sky + Emily GOTV');

					$mail->addAddress($user["email"], $user["first"]." ".$user["last"]);
					$mail->addReplyTo('gotv@skyandem.nu', 'Sky + Emily GOTV');
					$mail->isHTML(true);                                  // Set email format to HTML
					$mail->Subject = 'Sky + Emily Password Reset';

					$username = $user["username"];

					$mail->Body =
					$mail->AltBody = "You are receiving this email because a password reset request was issued for your account on our
				        campaign site.\r\n\r\n
				        Your username is $username. To reset your password, please use the following link: https://manage.skyandem.nu/reset/?link=$link.
				        \r\n\r\n
				        If you did not request this change, you can safely disregard this email.
				        \r\n\r\n
				        In service,\r\n
				        Sky + Emily";

					$mail->send();
					Campaign::msg("success", "A password reset link has been sent to your email.");
				} catch (Exception $e) {
					Campaign::msg("warning", "Something went wrong.. Error: ".$mail->ErrorInfo);
				}

				header("Location: .");
				die();
			}
			break;
		}
		case ("reset"): {
			if (!isset($_POST["link"])) {
				header("Location: .");
				die();
			}

			$link = $_POST["link"];
			$l = ResetLink::findBy("link", $link);
			if (!$l || $l->active == 0) {
				Campaign::msg("warning", "Invalid or expired link.");
				header("Location: .");
				die();
			}

			$l->update(["active" => 0]);

			$db = DB::getDBEngine();
			$db->update("users")->set(["password" => md5($_POST["password"])])->where("id", $l->user)->query();

			Campaign::msg("success", "Password reset!");
			header("Location: ../login");
			die;
		}
		default: {
			header("Location: .");
			die;
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php Campaign::head(); ?>
		<script>
			window.check = function() {
				return document.querySelector("#password").value === document.querySelector("#confirm").value;
			}
		</script>
	</head>
	<body class="login">
		<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
			<a class="navbar-brand" href="<?= Config::get("app-root"); ?>"><?= Config::get("campaign-name") ?></a>
		</nav>
		<div class="container-fluid h-100 d-flex align-items-center justify-content-center">
			<?php
			try {
				if (isset ($_GET["link"])):
				$link = ResetLink::findBy("link", $_GET["link"]);

				if ($link && $link->active != 0): ?>
				<form action="" method="POST" onsubmit="return window.check()">
					<h1 class="mb-3 text-center">Reset Your Password</h1>
					<input type="hidden" name="action" value="reset">
					<input type="hidden" name="link" value="<?= $_GET["link"] ?>">
					<input type="password" class="form-control mb-1" name="password" id="password" placeholder="Password">
					<input type="password" class="form-control mb-1" id="confirm" placeholder="Confirm">
					<input type="submit" class="form-control btn btn-primary w-100" value="Reset Password">
				</form>
				<? die(); else: Campaign::msg("warning", "Invalid or expired link."); endif; endif;
			} catch (Exception $ignored) {}
			?>
			<form action="" method="POST">
				<h1 class="mb-3 text-center">Reset Your Password</h1>
				<?php Campaign::message() ?>
				<input type="hidden" name="action" value="send">
				<input type="email" name="email" placeholder="Email" class="form-control mb-2">
				<input type="submit" value="Reset Password" class="form-control btn btn-primary w-100">
			</form>
		</div>
	</body>
</html>
