<?php
$src_dir = __DIR__ . "/data/content/";
$file_name = filter_var($_GET["fileName"], FILTER_SANITIZE_STRING);

$json = file_get_contents($src_dir . $file_name);
echo($json);
