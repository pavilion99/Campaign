<?php
/**
 * Created by PhpStorm.
 * User: me
 * Date: 2018-02-14
 * Time: 09:12
 */

class Slack {
	private static function get_hook_url(): string {
		return Config::get("slack-hook-address");
	}

	public static function submit_endorsement(Endorsement $endorsement) {
		$data = self::endorsement_button($endorsement);

		$c = curl_init(self::get_hook_url());

		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($data)]);

		curl_exec($c);
	}

	public static function endorsement_button(Endorsement $e) {
		return json_encode(
			["text" => "An endorsement has been received!\n\n*From*: ".$e->getSender()."\n\n*Title*: ".$e->getTitle()."\n*Message*: ".$e->getContent()."\n",
				"attachments" => [
					[
					"fallback" => "An endorsement has been received, but something went wrong. Check the admin panel.",
					"text" => "Would you like to approve or reject this endorsement?",
					"callback_id" => "endorsement_".$e->getID(),
					"attachment_type" => "default",
					"color" => "good",
					"actions" => [
						[
							"name" => "resolution",
							"text" => "Approve",
							"type" => "button",
							"value" => "approve",
							"confirm" => [
								"title" => "Are you sure?",
								"text" => "Please make sure you want to approve this endorsement. It will become visible on the campaign site immediately!",
								"ok_text" => "Approve",
								"dismiss_text" => "Go Back"
							]
						],
						[
							"name" => "resolution",
							"text" => "Reject",
							"type" => "button",
							"value" => "ignore"
						]
					]
				]
					]
			]
		);
	}

	public static function hook(string $url, string $message) {
		$data = $message;

		$c = curl_init(Config::get($url));

		curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_HTTPHEADER, ['Content-Type: application/json','Content-Length: '.strlen($data)]);

		curl_exec($c);
	}
}