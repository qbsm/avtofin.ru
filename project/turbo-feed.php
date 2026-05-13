<?php

ini_set("display_errors", 0);

$config = (include_once(__DIR__ . "/config.php"));

function readJSON($path) {
  return json_decode(file_get_contents($path), true);
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'avtofin.org';
$origin = $scheme . '://' . $host;

$promo = readJSON($config["data_dir"] . "/production/promo-production.json");
$globalData = readJSON($config["data_dir"] . "/production/global-production.json") ?: [];

$branchesDir = $config["data_dir"] . "/production/branches";
$branches = [];
foreach (($globalData['branches'] ?? []) as $slug) {
  if (!is_string($slug) || !preg_match('/^[a-z0-9_\-]+$/', $slug)) continue;
  $file = $branchesDir . '/' . $slug . '.json';
  if (!is_file($file)) continue;
  $b = readJSON($file);
  if (!is_array($b)) continue;
  if (isset($b['visible']) && $b['visible'] === false) continue;
  $branches[] = $b;
}

$globalPhone = $globalData['phone'] ?? null;

function findSection($data, $name) {
  foreach (($data['firstScreen'] ?? []) as $s) if (($s['name'] ?? '') === $name) return $s;
  foreach (($data['secondaryScreen'] ?? []) as $s) if (($s['name'] ?? '') === $name) return $s;
  return null;
}

$intro = findSection($promo, 'intro');
$advantages = findSection($promo, 'advantages');
$conditions = findSection($promo, 'conditions');
$accordion = findSection($promo, 'accordion');

function buildItem($promo, $intro, $advantages, $conditions, $accordion, $origin, $branch = null) {
  $city = $branch['city'] ?? '';
  $inCity = $branch ? (' в ' . ($branch['inName'] ?? $city)) : '';
  $slug = $branch['slug'] ?? '';
  $url = $origin . '/promo/' . ($slug ? $slug . '/' : '');

  $titleTpl = !empty($branch['title']) ? $branch['title'] : ($promo['title'] ?? '');
  $descTpl = !empty($branch['description:']) ? $branch['description:'] : ($promo['description'] ?? '');
  $title = str_replace(['%city%', '%inCity%'], [$city, $inCity], $titleTpl);
  $description = str_replace(['%city%', '%inCity%'], [$city, $inCity], $descTpl);

  $phoneHref = $branch['phone']['href'] ?? null;
  $phoneTitle = $branch['phone']['title'] ?? null;

  $heading = ($intro['heading'] ?? '') . $inCity;
  $subheading = $intro['subheading'] ?? '';
  $bullets = $intro['items'] ?? [];
  $button = $intro['button'] ?? 'Оставить заявку';

  ob_start();
  ?>
  <header>
    <h1><?= htmlspecialchars($heading, ENT_XML1, 'UTF-8') ?></h1>
    <h2><?= htmlspecialchars($subheading, ENT_XML1, 'UTF-8') ?></h2>
  </header>
  <?php if ($bullets): ?>
  <ul>
    <?php foreach ($bullets as $b): ?>
    <li><?= htmlspecialchars(strip_tags($b), ENT_XML1, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php if ($phoneHref): ?>
  <p>Телефон<?= $city ? ' филиала' . $inCity : '' ?>: <a href="tel:<?= htmlspecialchars($phoneHref, ENT_XML1, 'UTF-8') ?>"><?= htmlspecialchars($phoneTitle, ENT_XML1, 'UTF-8') ?></a></p>
  <?php endif; ?>
  <?php if ($branch && !empty($branch['address'])): ?>
  <p>Адрес: <?= htmlspecialchars(strip_tags($branch['address']), ENT_XML1, 'UTF-8') ?></p>
  <?php endif; ?>

  <?php if ($advantages && !empty($advantages['items'])): ?>
  <h2><?= htmlspecialchars($advantages['heading'] ?? 'Наши преимущества', ENT_XML1, 'UTF-8') ?></h2>
  <ul>
    <?php foreach ($advantages['items'] as $adv): ?>
    <li><?= htmlspecialchars(strip_tags($adv['title']), ENT_XML1, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <?php if ($conditions && !empty($conditions['items'])): ?>
  <h2><?= htmlspecialchars($conditions['heading'] ?? 'Условия', ENT_XML1, 'UTF-8') ?></h2>
  <?php if (!empty($conditions['subheading'])): ?>
  <p><?= htmlspecialchars($conditions['subheading'], ENT_XML1, 'UTF-8') ?></p>
  <?php endif; ?>
  <ul>
    <?php foreach ($conditions['items'] as $cond): ?>
    <li><?= htmlspecialchars(strip_tags($cond), ENT_XML1, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>
  <?php if (!empty($conditions['example'])): ?>
  <p><?= htmlspecialchars(strip_tags($conditions['example']), ENT_XML1, 'UTF-8') ?></p>
  <?php endif; ?>
  <?php if (!empty($conditions['giver'])): ?>
  <p><?= htmlspecialchars(strip_tags($conditions['giver']), ENT_XML1, 'UTF-8') ?></p>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($accordion && !empty($accordion['items'])): ?>
  <h2><?= htmlspecialchars($accordion['heading'] ?? 'Часто задаваемые вопросы', ENT_XML1, 'UTF-8') ?></h2>
  <?php foreach ($accordion['items'] as $faq): ?>
  <h3><?= htmlspecialchars(strip_tags($faq['title'] ?? ''), ENT_XML1, 'UTF-8') ?></h3>
  <p><?= htmlspecialchars(strip_tags($faq['desc'] ?? ($faq['content'] ?? '')), ENT_XML1, 'UTF-8') ?></p>
  <?php endforeach; ?>
  <?php endif; ?>

  <p><a href="<?= htmlspecialchars($url, ENT_XML1, 'UTF-8') ?>"><?= htmlspecialchars($button, ENT_XML1, 'UTF-8') ?></a></p>
  <?php
  $content = ob_get_clean();

  $pubDate = date(DATE_RSS);
  $turboContent = '<![CDATA[' . trim($content) . ']]>';

  return [
    'url' => $url,
    'title' => $title,
    'description' => $description,
    'pubDate' => $pubDate,
    'turboContent' => $turboContent,
  ];
}

$items = [];
$items[] = buildItem($promo, $intro, $advantages, $conditions, $accordion, $origin, null);
foreach ($branches as $branch) {
  $items[] = buildItem($promo, $intro, $advantages, $conditions, $accordion, $origin, $branch);
}

header('Content-Type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:turbo="http://turbo.yandex.ru" version="2.0">
<channel>
  <title><?= htmlspecialchars('АВТОФИНАНС — деньги под залог ПТС', ENT_XML1, 'UTF-8') ?></title>
  <link><?= htmlspecialchars($origin, ENT_XML1, 'UTF-8') ?></link>
  <description><?= htmlspecialchars($promo['description'] ?? '', ENT_XML1, 'UTF-8') ?></description>
  <language>ru</language>
<?php foreach ($items as $item): ?>
  <item turbo="true">
    <turbo:extendedHtml>true</turbo:extendedHtml>
    <link><?= htmlspecialchars($item['url'], ENT_XML1, 'UTF-8') ?></link>
    <title><?= htmlspecialchars($item['title'], ENT_XML1, 'UTF-8') ?></title>
    <description><?= htmlspecialchars($item['description'], ENT_XML1, 'UTF-8') ?></description>
    <pubDate><?= $item['pubDate'] ?></pubDate>
    <author>АВТОФИНАНС</author>
    <turbo:content><?= $item['turboContent'] ?></turbo:content>
  </item>
<?php endforeach; ?>
</channel>
</rss>
