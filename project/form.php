<?php
ini_set("display_errors", 1);
require_once(__DIR__ . "/vendor/autoload.php");

use Ismart\Form\Form;

$config = (include_once(__DIR__ . "/config.php"));
$data = $_POST;

file_put_contents('logs/form_'.date('Y-m-d_His').'_data.txt', print_r($data,true));

if (!empty($_POST["website"])) {
  http_response_code(400);
  die("spam");
}

$token = $_POST["form_token"] ?? "";
$parts = explode(".", $token, 2);
$tokenValid = false;
if (count($parts) === 2) {
  [$ts, $sig] = $parts;
  $expected = hash_hmac("sha256", $ts, $config["form_secret"]);
  if (hash_equals($expected, $sig) && ctype_digit($ts)) {
    $elapsed = time() - (int)$ts;
    if ($elapsed >= ($config["form_min_delay"] ?? 10) && $elapsed < 86400) {
      $tokenValid = true;
    }
  }
}
if (!$tokenValid) {
  http_response_code(400);
  die("token");
}
unset($data["form_token"], $data["website"]);

if (array_key_exists("Телефон", $data)) {
  $phone = trim($_POST["Телефон"]);

  if (!empty($phone)) {
    $config["mail"]["address"] = json_decode(file_get_contents("data/production/index-production.json"),JSON_UNESCAPED_UNICODE)["emailRecipients"];
  }
}

if (empty($phone)) die();

if (array_key_exists('city', $data)) {
  $city = $data['city'];
  $data['Филиал'] = $city;
  unset($data['city']);
  if (in_array($city, ['Другое', 'Не выбрано'])) {
    $config['mail']['address'] = ['autozaimorg@yandex.ru'];
  } else {
    $indexData = json_decode(file_get_contents('data/production/index-production.json'), true);
    foreach ($indexData['secondaryScreen'] ?? [] as $section) {
      if (($section['name'] ?? '') == 'branches') {
        foreach ($section['items'] ?? [] as $item) {
          if ($item['city'] == $city) {
            $recipients = $item['emailRecipients'];
            if ($recipients) {
              $config['mail']['address'] = $recipients;
            } else {
              $config['mail']['address'] = ['dev@ismart.pro'];
              $data['error'] = "Не найдены получатели для филиала  \"$city\"";
            }
          }
        }
      }
    }
  }
}

$form = new Form($config["mail"]);
$form->send($data, null, null);
