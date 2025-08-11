<?php

namespace App\Models;

class UserSettings
{
  public string $verification;
  public bool $saveSignedDocuments;

  public function __construct(array $data)
  {
    $this->verification = $data['verification'] ?? 'email';
    $this->saveSignedDocuments = $data['save_signed_documents'] ?? false;
  }
}