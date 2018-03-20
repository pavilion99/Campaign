<?php
require_once("../../../../assets/php/Campaign.php");
Campaign::setup();

if (!$_POST || empty($_POST))
	die(json_encode(["result" => "failure", "message" => "Unknown request."]));

switch ($_POST["type"]) {
	case "subscribe": {
		try {
			$c = Contact::findBy("mailchimp_id", $_POST["id"]);
			$c->update(["receives_emails" => 1]);
			die;
		} catch (Exception $e) {
			try {
				$c = Contact::findBy("email", strtolower($_POST["data"]["email"]));

				$c->update(["mailchimp_id" => $_POST["id"], "receives_emails" => 1]);
				die;
			} catch (Exception $ignored) {}
		}

		$email = strtolower($_POST["data"]["email"]);
		$first = $_POST["data"]["merges"]["FNAME"];
		$last = $_POST["data"]["merges"]["LNAME"];
		$res = preg_replace("/, /", "\r\n", $_POST["data"]["merges"]["ADDRESS"]);
		$school = $_POST["data"]["merges"]["SCHOOL"];

		$id = $_POST["id"];

		$vals = ["first" => $first, "last" => $last, "email" => $email, "residence" => $res, "mailchimp_id" => $id, "school" => $school];

		if ($_POST["data"]["merges"]["PHONE"] != "") {
			$vals["number"] = $_POST["data"]["merges"]["PHONE"];
		}

		Contact::create($vals);
		die(json_encode(["result" => "success", "message" => "Contact Created"]));
	}
	case "unsubscribe": {
		$email = strtolower($_POST["data"]["email"]);
		$id = $_POST["id"];

		$c = Contact::findBy("email", $email);

		if (!$c) {
			die(json_encode(["result" => "failure", "message" => "Contact not found."]));
		}

		if ($c->mailchimp_id != $id)
			$c->update(["mailchimp_id" => $id]);

		$c->update(["receives_emails" => 0]);
		die(json_encode(["result" => "success", "message" => "Contact Unsubscribed"]));
	}
	default: {
		die(json_encode(["result" => "failure", "message" => "Unknown request."]));
	}
}