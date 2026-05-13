<?php
ini_set("display_errors", 0);
require_once(__DIR__ . "/vendor/autoload.php");

use Ismart\Form\Form;

$config = (include_once(__DIR__ . "/config.php"));
$data = $_POST;

@file_put_contents('logs/turbo_'.date('Y-m-d_His').'_data.txt', print_r($data, true));

if (!empty($_POST["website"])) {
  http_response_code(400);
  die("spam");
}

$phone = trim($_POST["Телефон"] ?? $_POST["phone"] ?? "");
if ($phone === "" || !preg_match('/\d/', $phone)) {
  http_response_code(400);
  die("phone");
}
$data["Телефон"] = $phone;
unset($data["phone"]);

$data["Источник"] = "Яндекс.Турбо";

$indexData = json_decode(@file_get_contents("data/production/index-production.json"), true) ?: [];
$config["mail"]["address"] = $indexData["emailRecipients"] ?? ["autozaimorg@yandex.ru"];

if (!empty($_POST["city"])) {
  $city = trim($_POST["city"]);
  $data["Филиал"] = $city;
  unset($data["city"]);
  foreach ($indexData["secondaryScreen"] ?? [] as $section) {
    if (($section["name"] ?? "") !== "branches") continue;
    foreach ($section["items"] ?? [] as $item) {
      if (($item["city"] ?? "") === $city && !empty($item["emailRecipients"])) {
        $config["mail"]["address"] = $item["emailRecipients"];
      }
    }
  }
}

$form = new Form($config["mail"]);
$form->send($data, null, null);

http_response_code(200);
echo "ok";
