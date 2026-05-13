<?php

ini_set("display_errors", 1);
require_once(__DIR__ . "/vendor/autoload.php");

$config = (include_once(__DIR__ . "/config.php"));

function readJSON($path) {
  return json_decode(file_get_contents($path),JSON_UNESCAPED_UNICODE);
}

function render($templates_dir, $data, $page) {
  $loader = new \Twig\Loader\FilesystemLoader($templates_dir);
  $engine = new \Twig\Environment($loader);
  $engine->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Moscow');

  $page_name = $page ? $page : "index";

  return $engine->render("pages/" . $page_name . ".twig", $data);
}

function deepMerge($base, $override) {
  if (!is_array($base) || !is_array($override)) return $override;
  foreach ($override as $k => $v) {
    if (isset($base[$k]) && is_array($base[$k]) && is_array($v) && !array_is_list($base[$k])) {
      $base[$k] = deepMerge($base[$k], $v);
    } else {
      $base[$k] = $v;
    }
  }
  return $base;
}

function resolveScreen($names, $globalSections, $pageOverrides) {
  $result = [];
  foreach ((array)$names as $name) {
    if (!is_string($name)) continue;
    $base = $globalSections[$name] ?? ['name' => $name];
    $base['name'] = $name;
    $over = $pageOverrides[$name] ?? null;
    $result[] = is_array($over) ? deepMerge($base, $over) : $base;
  }
  return $result;
}

function getPageData($name) {
  global $config, $globalData;
  $page = readJSON($config["data_dir"] . "/production/$name-production.json");
  if (!is_array($page)) $page = [];
  $pageGlobals = $page['globals'] ?? [];
  $page['globals'] = array_merge(array_diff_key($globalData, ['sections' => 1]), $pageGlobals);
  $globalSections = $globalData['sections'] ?? [];
  $overrides = $page['sections'] ?? [];
  $page['firstScreen'] = resolveScreen($page['firstScreen'] ?? [], $globalSections, $overrides);
  $page['secondaryScreen'] = resolveScreen($page['secondaryScreen'] ?? [], $globalSections, $overrides);
  unset($page['sections']);
  return $page;
}

function loadGlobal() {
  global $config;
  $g = readJSON($config["data_dir"] . "/production/global-production.json");
  return is_array($g) ? $g : [];
}

function loadBranches($manifest) {
  global $config;
  $dir = $config["data_dir"] . "/production/branches";
  $result = [];
  foreach ($manifest as $slug) {
    if (!is_string($slug) || !preg_match('/^[a-z0-9_\-]+$/', $slug)) continue;
    $file = $dir . '/' . $slug . '.json';
    if (!is_file($file)) continue;
    $b = readJSON($file);
    if (!is_array($b)) continue;
    if (isset($b['visible']) && $b['visible'] === false) continue;
    $result[] = $b;
  }
  return $result;
}


$root = "";

$globalData = loadGlobal();

$path = explode('?', $_SERVER['REQUEST_URI'])[0];
$segments = array_values(array_filter(explode('/', str_replace($root, '', $path)), 'strlen'));

$indexData = getPageData('index');
$branches = loadBranches($indexData['globals']['branches'] ?? []);
$indexData['globals']['branches'] = $branches;

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

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$data["origin"] = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'avtofin.org');
$data["canonicalUrl"] = $data["origin"] . strtok($_SERVER['REQUEST_URI'], '?');
$data["currentBranch"] = $matchedBranch;

$tokenTs = time();
$tokenSig = hash_hmac("sha256", (string)$tokenTs, $config["form_secret"]);
$data["formToken"] = $tokenTs . "." . $tokenSig;

echo render($config["templates"], $data, $page_name);
