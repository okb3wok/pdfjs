<?php

namespace App;
use App\Models\User;
use App\Models\UserSettings;

class Controller
{

  public function createNewUser(string $email, string $name, string $password)
  {
    $db = load_data();
    if (isset($db[$email])) {
      return null;
    }
    $user = new User($email, [
      'name' => $name,
      'passwordHash' => getPasswordHash($password)
    ]);
    $user->settings = new UserSettings([]);
    $user->documents = [];
    $this->save_user_by_email($email, $user);
    return $user;
  }

  public function load_user_by_email(string $email) {
    $db = load_data();
    if (!isset($db[$email]) || !is_array($db[$email])) {
      return null;
    }
    $row = $db[$email];
    $user = new User($email, [
      'name' => $row['name'] ?? '',
      'password' => $row['password'] ?? '',
    ]);
    $user->email = $email;
    $user->registeredAt = $row['registered_at'];
    $user->lastLogin = date('Y-m-d H:i:s');
    $user->publicKey    = $row['public_key'];
    $user->passwordHash = $row['passwordHash'];
    $user->settings = isset($row['settings']) && is_array($row['settings'])
      ? new UserSettings($row['settings'])
      : new UserSettings([]);
    $user->documents = $row['documents'];
    return $user;
  }

  public function save_user_by_email(string $email, ?User $user) {
    $db = load_data();
    if (!is_array($db)) {
      $db = [];
    }
    $db[$email] = $user->toArray();
    save_data($db);
  }

}


//
//// Создаём пользователя
//$user = new App\Models\User(
//  'ra3wok@mail.ru',
//  [ 'name' => 'Александр Долженков', 'password' => 'sdfsdf']
//);
//
//
//// Создаём документ
//$doc = new Document(
//  '1dssfd-sdfsdf-sdfsd-sdfsdf',
//  'Документ 1',
//  'http://example.com/docs/1.pdf',
//  'abc123...',
//  true,
//  '2025-08-08T13:45:00Z',
//  [
//    'hash_signed' => 'sig-xyz...',
//    'ip' => '192.168.1.1',
//    'user_agent' => 'Firefox/141.0'
//  ]
//);
//
//// Добавляем документ пользователю
//$user->addDocument($doc);
//
//// Загружаем существующие данные из файла, если есть
//$data = [];
//if (file_exists(DB_FILE)) {
//  $content = file_get_contents(DB_FILE);
//  $data = json_decode($content, true) ?? [];
//}
//
//// Добавляем/обновляем пользователя
//$data[$user->email] = $user->toArray();
//
//// Сохраняем всё в файл
//save_data($data);