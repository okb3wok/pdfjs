<?php


function load_data() {
  if (file_exists(DB_FILE)) {
    $json = file_get_contents(DB_FILE);
    return json_decode($json, true); // true → ассоциативный массив
  }
  return [];
}

function save_data($data) {
  $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  file_put_contents(DB_FILE, $json);
}