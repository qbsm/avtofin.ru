<?php

ini_set("display_errors", 1);
require_once(__DIR__ . "/vendor/autoload.php");

$config = (include_once(__DIR__ . "/config.php"));

function readJSON($path) {
  return json_decode(file_get_contents($path),JSON_UNESCAPED_UNICODE);
}

function render($templates_dir, $data, $page) {
  $loader = new Twig_Loader_Filesystem($templates_dir);
  $engine = new Twig_Environment($loader);
  $engine->getExtension('Twig_Extension_Core')->setTimezone('Europe/Moscow');

  $page_name = $page ? $page : "index";

  return $engine->render("pages/" . $page_name . ".twig", $data);
}

function getPageData($name) {
  global $config;
  return readJSON($config["data_dir"] . "/production/$name-production.json");
}


$root = "";

$path = explode('?', $_SERVER['REQUEST_URI'])[0];
$segments = array_values(array_filter(explode('/', str_replace($root, '', $path)), 'strlen'));

$indexData = getPageData('index');
$branches = $indexData['globals']['branches'] ?? [];

$isPromo = (($segments[0] ?? '') === 'promo');
$citySlug = $isPromo ? ($segments[1] ?? '') : ($segments[0] ?? '');

$matchedBranch = null;
foreach ($branches as $item) {
  if ($citySlug && $citySlug === ($item['slug'] ?? '')) {
    $matchedBranch = $item;
    break;
  }
}

if ($isPromo) {
  $page_name = 'promo';
  $promoData = getPageData('promo');
  $data = $promoData;
  foreach ($indexData['globals'] ?? [] as $name => $value) {
    $data[$name] = $value;
  }
  $data['globals'] = $indexData['globals'] ?? [];
  $data['page'] = 'promo';
  $data['cities'] = array_column($branches, 'city');
  $data['city'] = '';
  $data['inCity'] = '';
  $data['citySlug'] = '';

  if ($citySlug) {
    if (!$matchedBranch) {
      http_response_code(404);
      exit();
    }
    $data['citySlug'] = $matchedBranch['slug'];
    $data['city'] = $matchedBranch['city'];
    $data['inCity'] = ' в ' . $matchedBranch['inName'];
    $data['branches'] = [$matchedBranch];
    $data['globals']['branches'] = [$matchedBranch];
    if (!empty($matchedBranch['title'])) {
      $data['title'] = $matchedBranch['title'];
    }
    if (!empty($matchedBranch['description:'])) {
      $data['description'] = $matchedBranch['description:'];
    }
    if (!empty($matchedBranch['emailRecipients'])) {
      $data['emailRecipients'] = $matchedBranch['emailRecipients'];
    }
  }
} else {
  $data = $indexData;
  $data['page'] = 'index';
  $data['city'] = '';
  $data['inName'] = '';
  $data['inCity'] = '';
  foreach ($indexData['globals'] ?? [] as $name => $value) {
    $data[$name] = $value;
  }
  $data['cities'] = array_column($branches, 'city');

  $page_name = $citySlug ?: 'index';
  if ($matchedBranch) {
    $data['citySlug'] = $matchedBranch['slug'];
    $data['city'] = $matchedBranch['city'];
    $data['inCity'] = ' в ' . $matchedBranch['inName'];
    $page_name = 'index';
  }

  if ($page_name != 'index') {
    http_response_code(404);
    exit();
  }
}

$disclaimer = file_get_contents($config["data_dir"] . "/content/disclaimer.html");
$manifest = readJSON($config["assets_dir"] ."/json/rev-manifest.json");
$data["manifest"] = $manifest;
$data["disclaimer"] = $disclaimer;

$tokenTs = time();
$tokenSig = hash_hmac("sha256", (string)$tokenTs, $config["form_secret"]);
$data["formToken"] = $tokenTs . "." . $tokenSig;

echo render($config["templates"], $data, $page_name);
