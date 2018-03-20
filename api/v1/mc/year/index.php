<?php
require_once("../../../../assets/php/Campaign.php");

$contacts = Contact::all();

/** @var Contact $contact */
foreach ($contacts as $contact)
{
	/** @var array $m */
	$m = [];
	preg_match("!\d+!", $contact->email, $m);
	$contact->update(["year" => $m[0]]);
}