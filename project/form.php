<?php
ini_set("display_errors", 1);
require_once(__DIR__ . "/vendor/autoload.php");

use Ismart\Form\Form;

$config = (include_once(__DIR__ . "/config.php"));
$data = $_POST;

file_put_contents('logs/form_'.date('Y-m-d_His').'_data.txt', print_r($data,true));

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
  if ($city == 'Другое') {
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
