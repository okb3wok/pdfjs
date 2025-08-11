<?php

namespace App\Models;

class User
{
  public string $email;
  public string $name;
  public string $registeredAt;
  public string $lastLogin;
  public string $publicKey;
  public string $passwordHash;
  public array $documents = [];
  public UserSettings $settings;

  public function __construct(string $email, array $data)
  {
    $this->email = $email;
    $this->name = $data['name'];
    $this->registeredAt = date('Y-m-d H:i:s');
    $this->lastLogin = date('Y-m-d H:i:s');
    $this->publicKey = '';
    $this->passwordHash = $data['passwordHash'] ?? '';
    $this->settings = new UserSettings(['verification' => 'email', 'download-signed-documents' => true]);
  }

  // Пример метода: проверить, подписан ли документ по id
  public function isDocumentSigned(string $docId): bool
  {
    foreach ($this->documents as $doc) {
      if ($doc->id === $docId) {
        return $doc->signed;
      }
    }
    return false;
  }

  public function toArray(): array
  {
    return [
      'name' => $this->name,
      'registered_at' => $this->registeredAt,
      'last_login' => $this->lastLogin,
      'public_key' => $this->publicKey,
      'passwordHash' => $this->passwordHash,
      'settings' => [
        'verification' => $this->settings->verification,
        'save_signed_documents' => $this->settings->saveSignedDocuments
      ],
      'documents' => $this->documents
    ];
  }


  public function addDocument(Document $document): void
  {

    $docArray = [
      'id'         => $document->id,
      'name'       => $document->name,
      'path'       => $document->path,
      'local_path' => $document->localPath ?? '',
      'hash'       => $document->hash ?? '',
      'signed'     => (bool) $document->signed,
      'signed_at'  => $document->signedAt ?? '',
      'signature'  => $document->signature
        ? [
          'hash_signed' => $document->signature->hashSigned,
          'ip'          => $document->signature->ip,
          'user_agent'  => $document->signature->userAgent,
        ]
        : null
    ];

    $this->documents[] = $docArray;
  }

  public function findDocumentById(string $id): ?array
  {
    if (!is_array($this->documents)) {
      return null;
    }

    foreach ($this->documents as $doc) {
      if (isset($doc['id']) && $doc['id'] === $id) {
        return $doc;
      }
    }

    return null;
  }

  public function updateDocumentById(string $id, array $newData): bool
  {
    if (!is_array($this->documents)) {
      return false;
    }

    foreach ($this->documents as $index => $doc) {
      if (isset($doc['id']) && $doc['id'] === $id) {
        $this->documents[$index] = array_merge($doc, $newData);
        return true;
      }
    }

    return false;
  }


}