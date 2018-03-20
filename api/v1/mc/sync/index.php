<?php
require_once("../../../../assets/php/Campaign.php");

$contacts = Contact::all();
$list = Config::get("mailchimp-list-id");
$key = Config::get("mailchimp-api-key");

echo "Getting members, this may take a while...";
$ts = microtime(true);
$members = MailChimp::get_members();
echo "done.\r\nMailChimp reports ".sizeof($members)." contacts. (".(microtime(true) - $ts)." s)\r\n";
echo "We have ".sizeof($contacts)." contacts.\r\n";
if (sizeof($contacts) > sizeof($members)) {
	echo "We have more contacts.\r\n";
} else if (sizeof($contacts) < sizeof($members)) {
	echo "MailChimp has more contacts.\r\n";
} else {
	echo "Contact count is equal.\r\n";
}

echo "Mapping contacts...";
$unique_id_map = [];
$email_map = [];
$done_map = [];

foreach ($members as $member) {
	$unique_id_map[$member["unique_email_id"]] = $member;
	$email_map[strtolower($member["email_address"])] = $member;
	$done_map[$member["unique_email_id"]] = false;
}
echo "done.\n\n";

/** @var Contact $contact */
foreach ($contacts as $contact) {
	$mc_contact = null;
	if (array_key_exists($contact->mailchimp_id, $unique_id_map)) {
		$mc_contact = $unique_id_map[$contact->mailchimp_id];
	} else if ($email_map[strtolower($contact->email)]) {
		$mc_contact = $email_map[strtolower($contact->mailchimp_id)];
		echo "Contact $contact->first $contact->last ($contact->id) found by email. Updating MailChimp id...";
		$contact->mailchimp_id = $mc_contact["unique_email_id"];
		echo "done.\r\n";
	} else {
		echo "Contact $contact->first $contact->last ($contact->id) did not exist in MailChimp's database. Creating...";
		MailChimp::register_contact($contact);
		echo "done.\r\n";
		continue;
	}

	$done_map[$mc_contact["unique_email_id"]] = true;

	if (calc_diffs($mc_contact, $contact)) {
		echo "Contact $contact->first $contact->last ($contact->id) had no differences between MailChimp and Management server. Skipping.\r\n";
		continue;
	}

	$manage_last_changed = new DateTime($contact->last_changed);
	$mc_last_changed = new DateTime($mc_contact["last_changed"]);

	if ($manage_last_changed >= $mc_last_changed) {
		echo "Contact $contact->first $contact->last ($contact->id) is newer on our servers. Updating MailChimp...";
		MailChimp::update_contact($contact);
		echo "done.\r\n";
	} else {
		echo "Contact $contact->first $contact->last ($contact->id) is newer on MailChimp's servers. Updating our servers...";
		$contact->update([
			"receive_emails" => $mc_contact["status"] == "subscribed" ? 1 : 0,
			"first" => $mc_contact["merge_fields"]["FNAME"],
			"last" => $mc_contact["merge_fields"]["LNAME"],
			"residence" => preg_replace("/, /", "\r\n", $mc_contact["merge_fields"]["ADDRESS"]),
			"number" => $mc_contact["merge_fields"]["PHONE"] == 0 ? null : $mc_contact["merge_fields"]["PHONE"],
			"school" => $mc_contact["merge_fields"]["SCHOOL"],
			"mailchimp_id" => $mc_contact["unique_email_id"],
			"year" => $mc_contact["merge_fields"]["YEAR"],
			"sl" => $mc_contact["interests"][MailChimp::SL_ID] ? 1 : 0,
			"vip" => $mc_contact["interests"][MailChimp::VIP_ID] ? 1 : 0
		]);
		echo "done.\r\n";
	}
}

foreach ($members as $member) {
	if ($done_map[$member["unique_email_id"]])
		continue;

	if ($member["unique_email_id"] == "" || $member["unique_email_id"] == null)
		continue;

	echo "Member ".$member["merge_fields"]["FNAME"]." ".$member["merge_fields"]["LNAME"]." (".$member["unique_email_id"].") was never processed. Processing now...";

	$contact = Contact::create([
		"email" => strtolower($member["email_address"]),
		"receive_emails" => $member["status"] == "subscribed" ? 1 : 0,
		"first" => $member["merge_fields"]["FNAME"],
		"last" => $member["merge_fields"]["LNAME"],
		"residence" => preg_replace("/, /", "\r\n", $member["merge_fields"]["ADDRESS"]),
		"number" => $member["merge_fields"]["PHONE"],
		"school" => $member["merge_fields"]["SCHOOL"],
		"mailchimp_id" => $member["unique_email_id"],
		"sl" => $member["interests"][MailChimp::SL_ID] == true ? 1 : 0,
		"vip" => $member["interests"][MailChimp::VIP_ID] == true ? 1 : 0,
		"year" => $member["merge_fields"]["YEAR"]
	]);

	echo "contact created (".$contact->id.").\r\n";
}

function calc_diffs($mc_contact, $manage_contact) {
	$fname = $mc_contact["merge_fields"]["FNAME"] == $manage_contact->first;
	$lname = $mc_contact["merge_fields"]["LNAME"] == $manage_contact->last;
	$phone = $mc_contact["merge_fields"]["PHONE"] == $manage_contact->number;
	if ($mc_contact["merge_fields"]["PHONE"] == "" && $manage_contact->number == null)
		$phone = true;
	$school = $mc_contact["merge_fields"]["SCHOOL"] == $manage_contact->school;
	$unique_id = $mc_contact["unique_email_id"] == $manage_contact->mailchimp_id;
	$year = intval($mc_contact["merge_fields"]["YEAR"]) == intval($manage_contact->year);
	$sl = ($mc_contact["interests"][MailChimp::SL_ID] ? 1 : 0) == $manage_contact->sl;
	$vip = ($mc_contact["interests"][MailChimp::VIP_ID] ? 1 : 0) == $manage_contact->vip;

	// echo "$fname $lname $phone $school $unique_id $year $sl";

	return $fname && $lname && $phone && $school && $unique_id && $year && $sl;
}