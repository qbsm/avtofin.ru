<?php
ini_set("display_errors", 1);
require_once(__DIR__ . "/vendor/autoload.php");

use Ismart\Form\Form;

$config = (include_once(__DIR__ . "/config.php"));
$data = $_POST;

if (array_key_exists("Телефон", $data)) {
  $phone = trim($_POST["Телефон"]);

  if (!empty($phone)) {
    $config["mail"]["address"] = json_decode(file_get_contents("data/production/index-production.json"),JSON_UNESCAPED_UNICODE)["emailRecipients"];
  }
}


$form = new Form($config["mail"]);
$form->send($data, null, null);
