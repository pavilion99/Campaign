<?php
require_once("../../../../assets/php/Campaign.php");
Campaign::setup();

$hMap = [
    "584 Lincoln St" => "AEPi",
    "2349 Sheridan Rd" => "BThPi",
    "619 Colfax St" => "DX",
    "2317 Sheridan Rd" => "DTD",
    "2307 Sheridan Rd" => "DU",
    "2339 Sheridan Rd" => "LXA",
    "2347 Sheridan Rd" => "PhDT",
    "2331 Sheridan Rd" => "PhGD",
    "2247 Sheridan Rd" => "PhKPs",
    "626 Emerson Pl" => "PhMA",
    "2313 Sheridan Rd" => "PKA",
    "2249 Sheridan Rd" => "SX",
    "2341 Sheridan Rd" => "SPhE",
    "2335 Sheridan Rd" => "SN",
    "2251 Sheridan Road" => "ZBT",
    "637 University Place" => "AXOme",
    "701 University Place" => "APh",
    "1870 Orrington Ave" => "XOme",
    "625 University Place" => "DDD",
    "618 Emerson Street" => "DG",
    "717 University Place" => "DZ",
    "640 Emerson Street" => "GPhB",
    "619 University Place" => "KD",
    "1871 Orrington Ave" => "KKG",
    "636 Emerson Street" => "PBPh",
    "710 Emerson Place" => "ZTA"
];

$contacts = Contact::all();

/** @var Contact $contact */
foreach ($contacts as $contact) {
    /**
     * @var string s$address
     * @var string $greek
     */
    foreach ($hMap as $address => $greek) {
        if (strpos($contact->residence, $address) !== false) {
            $contact->update(["greek" => $greek]);
        }
    }
}
