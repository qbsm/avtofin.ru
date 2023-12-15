<?php
ini_set("display_errors", 1);

return [
  "templates" => __DIR__ . "/templates",
  "data_dir" => __DIR__ . "/data",
  "assets_dir" => __DIR__ . "/assets",
  "mail" => [
    "templates" => __DIR__ . "/templates/emails",
    "addressCC" => ["leads@ismart.pro"],
  ]
];
