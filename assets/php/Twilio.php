<?php
require_once(__DIR__."/../lib/Twilio/autoload.php");

use Twilio\Rest\Client;

class Twilio {
  private static function getSID(): ?string {
    return Config::get("twilio.sid");
  }
  
  private static function getAuthToken(): ?string {
    return Config::get("twilio.auth-token");
  }
  
  public static function send(string $text, array $contacts): array {
    $sql = DB::get();

    $sid = self::getSID();
    $token = self::getAuthToken();
    $client = null;
    
    try {
      $client = new Client($sid, $token);
      echo "[TWILIO] Client configured successfully.\r\n";
    } catch (\Twilio\Exceptions\ConfigurationException $e) {
      if (php_sapi_name() == "cli") {
        echo "[TWILIO] Failure configuring Twilio client: ".$e->getCode().". Contact the Tech Chair with this information.";
        die;
      }
      Campaign::msg("warning", "Failure configuring Twilio client: ".$e->getCode().". Contact the Tech Chair with this information.");
      die;
    }

    $to = [];
    $errors = [];
    
    foreach ($contacts as $contact) {
      if ($contact->number == null || $contact->number == 0)
        continue;
      
      echo "[TWILIO] Adding contact $contact->first $contact->last ($contact->id, $contact->number) to the list of contacts.\r\n";
      $to[$contact->id] = $contact;
    }

    $phones = [];
    foreach ($client->incomingPhoneNumbers->read() as $phone) {
      $phones[] = $phone;
      echo "[TWILIO] Added available phone $phone->phoneNumber.\r\n";
    }

    $multi = ceil(sizeof($to) / sizeof($phones));

    echo "[TWILIO] Begininng message sending...check returned values for error reports.\r\n";
    $i = 1;
    $c = 0;
    $sent = [];
    foreach ($to as $id => $d) {
      try {
        $client->messages->create(
          "+1".$d->number,
          [
            'from' => $phones[$c]->phoneNumber,
            'body' => $text
          ]
        );
        $sent[] = $d;
      } catch (Exception $e) {
        $errors[$id] = $e->getCode()." ".$e->getMessage();
      }

      if ($i == $multi) {
        $i = 0;
        $c++;
      }

      $i++;
    }
  
    echo "[TWILIO] Done sending messages.";
    echo "[TWILIO] Encountered ".sizeof($errors)." errors.";
    return ["sent" => $to, "errors" => $errors];
  }
}