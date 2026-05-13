<?php
$src_dir = __DIR__ . "/data/content/";
$file_name = htmlspecialchars($_GET["fileName"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$json = file_get_contents($src_dir . $file_name);
echo($json);
