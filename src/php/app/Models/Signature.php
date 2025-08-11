<?php

namespace App\Models;
class Signature
{
  public string $hashSigned;
  public string $ip;
  public string $userAgent;

  public function __construct(array $data)
  {
    $this->hashSigned = $data['hash_signed'] ?? '';
    $this->ip = $data['ip'] ?? '';
    $this->userAgent = $data['user_agent'] ?? '';
  }
}