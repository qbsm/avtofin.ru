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

$query_string = $_SERVER['QUERY_STRING'];
$page_name =
  explode(
    '/',
    str_replace(
      $root,
      '',
      explode(
        '?',
        $_SERVER['REQUEST_URI']
      )[0]
    )
  )[1];
$page_name = $page_name ? $page_name : "index";
//$data = getPageData($page_name);

$indexData = getPageData('index');
$data = $indexData;
$data['page'] = 'index';
$data['city'] = '';
$data['inName'] = '';
foreach ($indexData['globals'] ?? [] as $name => $value) {
    $data[$name] = $value;
}
$branches = $indexData['globals']['branches'] ?? [];
$data['cities'] = array_column($branches, 'city');
foreach ($branches as $item) {
  if ($page_name == ($item['slug'] ?? '')) {
    $data['citySlug'] = $item['slug'];
    $data['city'] = $item['city'];
    $data['inCity'] = ' в '.$item['inName'];
  }
}
if ($data['city']) {
  $page_name = 'index';
}

if ($page_name != 'index') {
  http_response_code(404);
  exit();
}

$disclaimer = file_get_contents($config["data_dir"] . "/content/disclaimer.html");
$manifest = readJSON($config["assets_dir"] ."/json/rev-manifest.json");
$data["manifest"] = $manifest;
$data["disclaimer"] = $disclaimer;

$tokenTs = time();
$tokenSig = hash_hmac("sha256", (string)$tokenTs, $config["form_secret"]);
$data["formToken"] = $tokenTs . "." . $tokenSig;

echo render($config["templates"], $data, $page_name);
