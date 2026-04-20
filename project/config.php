<?php
ini_set("display_errors", 1);

return [
  "templates" => __DIR__ . "/templates",
  "data_dir" => __DIR__ . "/data",
  "assets_dir" => __DIR__ . "/assets",
  "form_secret" => "avtofin-form-2026-04-20-5c3b1a9e8d",
  "form_min_delay" => 10,
  "mail" => [
    "templates" => __DIR__ . "/templates/emails",
    "addressCC" => [""],
  ]
];
