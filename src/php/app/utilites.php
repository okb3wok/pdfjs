<?php

function getPasswordHash(string $password): string {
  // Используем алгоритм bcrypt (по умолчанию) с опцией cost = 12 (рекомендуемое значение)
  return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}


function verifyPassword(string $password, string $hash): bool {
  return password_verify($password, $hash);
}


function generateUuidV4(): string {
  $data = random_bytes(16);
  // Версия 4 — случайные биты
  $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
  // Устанавливаем биты для варианта RFC 4122
  $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function downloadFileContent(string $url): string {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60);
  curl_setopt($ch, CURLOPT_FAILONERROR, true);

  $data = curl_exec($ch);
  if ($data === false) {
    throw new Exception("Ошибка загрузки файла: " . curl_error($ch));
  }

  curl_close($ch);
  return $data;
}

function downloadFile(string $url, string $savePath): bool {
  $ch = curl_init($url);
  $fp = fopen($savePath, 'w+');

  if ($fp === false) {
    return false;
  }

  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60);
  curl_setopt($ch, CURLOPT_FAILONERROR, true);

  $result = curl_exec($ch);
  $error  = curl_error($ch);

  curl_close($ch);
  fclose($fp);

  if ($result === false) {
    unlink($savePath); // удаляем пустой файл
    throw new Exception("Ошибка загрузки файла: {$error}");
  }

  return true;
}


function isCorrectFileExtension(string $url): ?string {
  $path = parse_url($url, PHP_URL_PATH);
  if (!$path) return false;

  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  if (in_array($ext, ['pdf'])) {
    return true;
  }

  return false;
}
