<?php
class MailChimp {
	private const TOP_URL = "https://us12.api.mailchimp.com/3.0";

	public const SL_ID = "ff4227d40b";
	public const VIP_ID = "a0d9bcef43";

	private static function key(): string {
		return Config::get("mailchimp-api-key");
	}

	private static function list(): string {
		return Config::get("mailchimp-list-id");
	}

	private static function api_command(string $path, string $body, string $type): array {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::TOP_URL.$path);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		if ($type != "GET")
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_USERPWD, "scolton99:".self::key());
		curl_setopt($ch, CURLOPT_HEADER, 1);

		$t = curl_exec($ch);

		$js = substr($t, curl_getinfo($ch, CURLINFO_HEADER_SIZE), strlen($t));
		$res = json_decode($js, true);

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$error = $code > 399;

		return [
			"error" => $error,
			"res" => $res
		];
	}

	public static function register_contact(Contact $contact): ?string {
		$cmd = "/lists/".self::list()."/members";

		$body = json_encode([
			"email_address" => strtolower($contact->email),
			"status" => "subscribed",
			"merge_fields" => [
				"FNAME" => $contact->first,
				"LNAME" => $contact->last,
				"ADDRESS" => preg_replace("/\n/", ", ", $contact->residence),
				"PHONE" => $contact->number == null ? "" : $contact->number,
				"SCHOOL" => $contact->school,
				"YEAR" => $contact->year
			],
			"interests" => [
				self::SL_ID => $contact->sl == 1,
				self::VIP_ID => $contact->vip == 1
			]
		]);

		$result = self::api_command($cmd, $body, "POST");

		if ($result["error"]) {
			throw new CampaignException("Couldn't sync new contact to MailChimp. MailChimp reported: ".$result["res"]["detail"]);
		} else {
			return $result["res"]["unique_email_id"];
		}
	}

	public static function update_contact(Contact $contact) {
		$id = $contact->mailchimp_id;
		$hash = md5(strtolower($contact->email));

		$cmd = "/lists/".self::list()."/members/".$hash;

		$fields = [
			"email_address" => strtolower($contact->email),
			"status" => $contact->receive_emails == 1 ? "subscribed" : "unsubscribed",
			"merge_fields" => [
				"FNAME" 	=> $contact->first,
				"LNAME" 	=> $contact->last,
				"ADDRESS" 	=> preg_replace("/\n/", ", ", $contact->residence),
				"PHONE"		=> $contact->number == null ? "" : $contact->number,
				"SCHOOL" 	=> $contact->school,
				"YEAR"		=> $contact->year
			],
			"interests" => [
				self::SL_ID => $contact->sl == 1,
				self::VIP_ID => $contact->vip == 1
			]
		];

		$body = json_encode($fields);

		$result = self::api_command($cmd, $body, "PUT");

		if ($result["error"]) {
			throw new CampaignException("warning", "Couldn't update contact details in MailChimp. Error: ".$result["res"]["detail"]);
		}

		$m_id = $result["res"]["unique_email_id"];
		if ($id != $m_id) {
			$contact->update(["mailchimp_id" =>$m_id]);
		}
	}

	public static function get_members(): array {
		$path = "/lists/".self::list()."/members/?count=8000";

		$result = self::api_command($path, "", "GET");

		if ($result["error"])
			throw new CampaignException("warning", "Unable to get members.");

		return $result["res"]["members"];
	}

	public static function get_single_member(string $email) {
		$hash = md5(strtolower($email));
		$path = "/lists/".self::list()."/members/$hash";

		$result = self::api_command($path, "", "GET");

		if ($result["error"])
			throw new CampaignException("warning", "Unable to get member.");

		return $result["res"];
	}
}