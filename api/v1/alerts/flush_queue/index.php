<?php
require_once("../../../../assets/php/Campaign.php");
require_once("../../../../assets/lib/Twilio/autoload.php");
Campaign::setup();

$sql = DB::get();
$eng = new MySQL($sql);

echo "Getting all unsent alerts...";
$res = $sql->query("SELECT * FROM `alerts` WHERE `cron_confirm`=0 AND `planned` IS NOT NULL AND `sent` IS NULL");
echo "found ".$res->num_rows.".\r\n";

if ($res->num_rows == 0)
	return;

$now = new DateTime("now");

$contacts = Contact::all();

if ($res->num_rows > 0) {
	while ($alert = $res->fetch_assoc()) {
		if ($now >= new DateTime($alert["planned"])) {
			echo "Handing off to Twilio to send alert...\r\n";
			$twilioRes = Twilio::send($alert["content"], $contacts);
			echo "Alert sent.\r\n";
			
			$errors = $twilioRes["errors"];
			$sent = $twilioRes["sent"];
			
			echo "Creating interaction...";
			$i = Interaction::create(["interaction_category_id" => "5", "description" => $_POST["message"]]);
			echo "done (id: $i->id).\r\n";

			$failed = [];
			foreach ($sent as $contact) {
				if (!array_key_exists($contact->id, $errors)) {
					$l = InteractionLink::create(["interaction_id" => $i->id, "affiliation" => "0", "contact_id" => $contact->id]);
					echo "Created interaction link with contact $contact->id.\r\n";
				} else {
					$failed[] = $contact;
				}
			}
			
			if (!empty($failed)) {
				echo "Alert \"".$alert["title"]."\" (".$alert["id"].", ".$alert["content"].") failed to send to the following users: \r\n";
				
				foreach ($failed as $contact) {
					echo "Couldn't send to contact $contact->first $contact->last ($contact->id, $contact->number). Error: ".$errors[$contact->id];
					echo "\r\n";
				}
				
				echo "\r\n";
			} else {
				echo "Alert \"".$alert["title"]."\" (".$alert["id"].", ".$alert["content"].") sent successfully.\r\n\r\n";
			}
			
			$eng->update("alerts")->set(["sent" => (new DateTime()), "cron_confirm" => 1])->where("id", $alert["id"])->query();
		}
	}
}