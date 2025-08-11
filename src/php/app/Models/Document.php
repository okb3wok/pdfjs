<?php
namespace App\Models;

class Document
{
  public string $id;
  public string $name;
  public string $path;
  public string $local_path;
  public string $hash;
  public bool $signed;
  public string $signedAt;
  public ?Signature $signature;

  public function __construct(array $data)
  {
    $this->id = $data['id'];
    $this->name = $data['name'];
    $this->path = $data['path'];
    $this->local_path = $data['local_path'];
    $this->hash = $data['hash'];
    $this->signed = $data['signed'];
    $this->signedAt = $data['signed_at'];
    $this->signature = isset($data['signature']) && $data['signature'] !== null
      ? new Signature($data['signature'])
      : null;
  }


}