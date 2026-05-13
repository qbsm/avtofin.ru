<?php
ini_set("display_errors", 1);
$src_dir = __DIR__ . "/data/content/";
$output_dir = __DIR__ . "/data/production/";

$status = htmlspecialchars($_GET["valid"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$file_name = htmlspecialchars($_GET["fileName"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$newfile = str_replace('.json', '-production.json', $file_name);

if ($status) {
  if (!copy($src_dir . $file_name, $output_dir . $newfile)) {
    echo "Скопировать не удалось";
  } else {
    echo "Файл скопирован";
  }
}
