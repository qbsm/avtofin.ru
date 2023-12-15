<?php
ini_set("display_errors", 1);
$src_dir = __DIR__ . "/data/content/";
$output_dir = __DIR__ . "/data/production/";

$status = filter_var($_GET["valid"], FILTER_SANITIZE_STRING);
$file_name = filter_var($_GET["fileName"], FILTER_SANITIZE_STRING);

$newfile = str_replace('.json', '-production.json', $file_name);

if ($status) {
  if (!copy($src_dir . $file_name, $output_dir . $newfile)) {
    echo "Скопировать не удалось";
  } else {
    echo "Файл скопирован";
  }
}
