<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__."/../lib/PHPMailer/PHPMailer.php");
require_once(__DIR__."/../../assets/lib/PHPMailer/POP3.php");
require_once(__DIR__."/../../assets/lib/PHPMailer/Exception.php");
require_once(__DIR__."/../../assets/lib/PHPMailer/OAuth.php");
require_once(__DIR__."/../../assets/lib/PHPMailer/SMTP.php");

class CampaignMail {
	private const MAIL_SERVER = "sub5.mail.dreamhost.com";
	private const MAIL_PORT = 587;
	private const MAIL_GOTV_USERNAME = "gotv@skyandem.nu";
	private const MAIL_GOTV_PASSWORD = "*N6kX4mi";
	private const MAIL_ACCOUNT_USERNAME = "accounts@skyandem.nu";
	private const MAIL_ACCOUNT_PASSWORD = "Qq!MiFmL";

	public const ACCOUNT_GOTV = 0x0;
	public const ACCOUNT_ACCOUNTS = 0x1;

	public static function send(string $content, string $subject, string $recipientName, string $recipientEmail, int $account) {
		$username = null;
		$password = null;

		switch ($account) {
			case (self::ACCOUNT_GOTV): {
				$username = self::MAIL_GOTV_USERNAME;
				$password = self::MAIL_GOTV_PASSWORD;
				break;
			}
			case (self::ACCOUNT_ACCOUNTS): {
				$username = self::MAIL_ACCOUNT_USERNAME;
				$password = self::MAIL_ACCOUNT_PASSWORD;
				break;
			}
			default: {
				throw new CampaignException("Incorrect Account Specified", "");
			}
		}

		try {
			$mail = new PHPMailer();
			$mail->isSMTP();                            // Set mailer to use SMTP
			$mail->Host = self::MAIL_SERVER;    // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                     // Enable SMTP authentication
			$mail->Username = $username;
			$mail->Password = $password;
			$mail->SMTPSecure = 'tls';                  // Enable TLS encryption, `ssl` also accepted
			$mail->Port = self::MAIL_PORT;                          // TCP port to connect to

			//Recipients
			$mail->setFrom("$username", 'Sky + Emily GOTV');

			$mail->addAddress($recipientEmail, "$recipientName");
			$mail->addReplyTo("$username", 'Sky + Emily GOTV');
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;

			$mail->Body = "
				<div style=\"margin: auto; width: 720px; font-family:Georgia,sans-serif; color:#222; font-size: 15px;\">
				    <img src=\"http://via.placeholder.com/720x150\" style=\"margin-bottom:10px;\">";
			$mail->Body .= $content;
			$mail->Body .= "</div>";
			$mail->AltBody = strip_tags($mail->Body);

			$mail->send();
			Campaign::msg("success", "User provisioned. Message has been sent.");
		} catch (Exception $e) {
			throw new CampaignException("Mail could not be sent. Error: ".$mail->ErrorInfo);
		}
	}
}