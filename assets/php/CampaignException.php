<?php
class CampaignException extends Exception {
  private $redirect, $fatal;
  
  public function __construct($message, $redirect = "error", $fatal = false) {
    $this->redirect = $redirect;
    $this->fatal = $fatal;
    
    parent::__construct($message, 0, null);
  }
  
  public function getRedirect(): string {
    return $this->redirect;
  }
  
  public function isFatal(): bool {
    return $this->fatal;
  }
}