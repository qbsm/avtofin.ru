# avtofin.org — `project/`

Прод-копия сайта **avtofin.org** (займы под залог ПТС, ООО АВТОФИНАНС).
Это раскатанная папка `project/` из репо `qbsm/avtofin.ru`.
Сборка фронта (gulp+webpack) живёт в соседнем `dev/` — отсюда сюда копируются `templates/` и `assets/`.

- Прод-хост: `ssh -p 8228 s244000@ruvip54.hostiman.ru`, путь `www/avtofin.org/`
- PHP 8.4, Twig 3.24 (на проде), Twig 1.42 (локально — несовместимы по namespace)
- Деплой: **не git pull**, а точечный rsync/scp по списку изменённых файлов

---

## Структура

```
project/
├── index.php           # маршрутизатор + рендер Twig
├── form.php            # обработка основной формы заявки (HMAC-токен)
├── turbo-feed.php      # RSS-фид Яндекс.Турбо (rewrite на /turbo.xml)
├── turbo-form.php      # обработчик callback-формы из Турбо
├── copy.php            # копирование файлов с других сайтов экосистемы
├── read-json.php       # CRUD JSON через GET/POST
├── json.php            # legacy-эндпоинт, исторический
├── config.php          # пути + form_secret + smtp
├── .htaccess           # rewrite: /turbo.xml → turbo-feed.php, /* → index.php
├── robots.txt
│
├── data/
│   ├── content/        # «исходные» JSON и контентные ассеты
│   │   ├── global.json
│   │   ├── index.json
│   │   ├── promo.json
│   │   ├── disclaimer.html
│   │   └── ...
│   ├── production/     # «боевые» копии, отдаются на рантайме
│   │   ├── global-production.json
│   │   ├── index-production.json
│   │   ├── promo-production.json
│   │   └── branches/   # 17 филиалов, по одному файлу: tver.json, irkutsk.json…
│   ├── img/            # картинки контента (intro, advantages, actions, ui, …)
│   └── docs/           # PDF (политика, согласие, ЕГРЮЛ)
│
├── templates/          # Twig (копируется из dev/ при npm run build)
│   ├── layout.twig
│   ├── pages/
│   │   ├── index.twig
│   │   └── promo.twig
│   ├── parts/          # секции — header, nav, intro, actions, advantages, calc,
│   │   │               # conditions, reviews, accordion, branches, footer, modal,
│   │   │               # form, logo, *-critical (inline CSS)
│   └── emails/
│       └── email.twig  # шаблон письма заявки
│
├── assets/             # собранные css/js (revisioned)
│   ├── css/{index,promo}-<hash>.css
│   ├── js/{index,promo}-<hash>.js
│   └── json/rev-manifest.json
│
├── vendor/             # composer (Twig, PHPMailer, Ismart\Form)
└── logs/               # form_*.txt (заявки), turbo_*.txt (Турбо-заявки)
```

---

## Маршрутизация

`.htaccess` отдаёт всё что не файл/не каталог в `index.php?uri=$1`, кроме точного `/turbo.xml`.

| URL                       | Что отдаётся                                                  |
|---------------------------|---------------------------------------------------------------|
| `/`                       | `pages/index.twig` — главная                                  |
| `/<city-slug>/`           | главная с дефолтным `currentBranch` = филиал (для шаблонов SEO) |
| `/promo/`                 | `pages/promo.twig` — общая промо-посадочная                   |
| `/promo/<city-slug>/`     | promo + `matchedBranch` зашит в данные, `title/description` из филиала |
| `/turbo.xml`              | `turbo-feed.php` — RSS Турбо-фид                              |
| `/form.php`               | POST основной формы (HMAC-токен обязателен)                   |
| `/turbo-form.php`         | POST callback-формы из Яндекс.Турбо                           |

Несовпавший city-slug → 404.

---

## Данные

### Иерархия объединения

`getPageData($name)` в `index.php`:

1. читает `data/production/<name>-production.json`
2. читает `data/production/global-production.json`
3. в `firstScreen`/`secondaryScreen` каждая секция = `{name, ...overrides}`
4. для каждой секции базой берётся `globalData.sections[name]`, поверх — overrides из page-json (`deepMerge`)
5. `page.globals` = всё из `globalData` (кроме `sections`) + `page.globals` (override)

Это значит: **редактировать контент филиалов/секций нужно в `data/production/*.json`**, а не дёргать шаблоны.

### `content/` vs `production/`

| Папка        | Назначение                                                       |
|--------------|------------------------------------------------------------------|
| `content/`   | «исходники», то что было в репо изначально; служит для бэкапа    |
| `production/`| то, что реально отдаётся; на проде правится через `read-json.php`/админку и через ssh |

Локально нельзя слепо перезаписывать `data/production/*.json` — на проде контент часто свежее.

### Филиалы

`globalData.branches` — массив slug'ов (`["tver","irkutsk",...]`).
Каждый файл `data/production/branches/<slug>.json`:

```json
{
  "visible": true,
  "city": "Тверь",
  "slug": "tver",
  "inName": "Твери",
  "emailRecipients": ["tver@autosaim.su", "autozaimorg@yandex.ru"],
  "address": "пр-т Ленина, д. 26А, Тверь",
  "location": "56.851067, 35.840857",
  "yandex": "https://yandex.ru/maps/-/...",
  "working": "Пн-Пт 09:00-20:00<br>Сб-Вс 09:00-19:00",
  "phone": {"href": "+79961350078", "title": "+7 (996) 135-00-78"},
  "title": "опционально — override SEO title для /promo/<slug>/",
  "description": "опционально — override SEO description"
}
```

`visible: false` → исключается из рендера и из Турбо-фида.

---

## Формы

### Основная форма (`form.php`)

- Защита от ботов: honeypot `website` + HMAC-токен `form_token`.
- Токен генерится в `index.php:174-176`:
  `formToken = timestamp + "." + hmac_sha256(timestamp, form_secret)`.
- Сервер проверяет: подпись, `form_min_delay` (10s от рендера), TTL 86400s.
- Поле `Телефон` обязательно (на русском, по нему роутятся получатели).
- Поле `city` → `Филиал` + смена `emailRecipients` на филиальные.
- Логи: `logs/form_<datetime>_data.txt`.

### Turbo callback (`turbo-form.php`)

- Отдельный endpoint, **без HMAC-токена** — Яндекс не имеет нашего секрета.
- Защита: honeypot `website`, базовая проверка телефона (`/\d/`).
- Поля приходят из `<form data-type="callback">` в Турбо: `Телефон`, `Имя`, опц. `city`.
- Помечает заявку `Источник = Яндекс.Турбо`.
- Роутинг получателей по `city` (то же поведение, что и в `form.php`).
- Логи: `logs/turbo_<datetime>_data.txt`.

---

## Яндекс.Турбо

`turbo-feed.php` → `/turbo.xml` (через rewrite в `.htaccess`).
Отдаёт RSS 2.0 с `<turbo:content>` для каждого:

- общей промо-страницы (`/promo/`)
- 17 филиалов (`/promo/<slug>/`) — итерация по `branches`

Каждый `<item>` содержит:
- `<header>` с h1/h2 (intro)
- `<figure><img>` (intro/car1.png)
- список преимуществ
- телефон/адрес филиала
- секции actions, advantages, conditions, accordion
- callback-форму (`data-type="callback"` → `formaction=/turbo-form.php`)
- ссылку на каноническую `/promo/<slug>/`

`pubDate` берётся из `filemtime` исходных JSON (стабильно между запросами, без шума).

Все строки прогоняются через `cleanText()`: `html_entity_decode → str_replace <br> → strip_tags → normalize whitespace` — иначе `&#8239;` (узкий пробел) попадает в CDATA двойным эскейпом.

**Регистрация фида:** через Я.Вебмастер → Турбо-страницы → RSS-источники. Подключение в Директе — отдельный процесс.

---

## Эндпоинты-утилиты

| Файл              | Что делает                                                       |
|-------------------|------------------------------------------------------------------|
| `copy.php`        | копирует файл из другого сайта экосистемы по slug                |
| `read-json.php`   | GET/POST к `data/production/*.json` (используется админкой)      |
| `json.php`        | legacy-обработчик, исторический (≈750 строк)                     |

Все три отдают `text/json` или редиректят. На проде PHP 8.4 → в `read-json.php` уже `htmlspecialchars` вместо удалённого `FILTER_SANITIZE_STRING`; локальная версия может отставать.

---

## Twig: 3 vs 1

- **Прод:** `Twig 3.24` — namespace `\Twig\Loader\FilesystemLoader`
- **Локально:** `Twig 1.42` — namespace `Twig_Loader_Filesystem`

`render()` в `index.php` обходит это через `class_exists` (если есть — текущая ветка кода уже на Twig 3, локальная подтянет позже). При правке функции — оставлять обе ветки рабочими, либо синхронно поднять локальный `composer`.

---

## Деплой

1. В `dev/` собрать: `npm run build` — обновит `project/templates/parts/*.twig` и `project/assets/{css,js}/*` плюс `rev-manifest.json`.
2. Закоммитить только релевантные файлы (не bulk-add), `team@ismart.pro` в авторе:
   ```bash
   git -c user.name="team" -c user.email="team@ismart.pro" commit -m "..."
   ```
3. Пуш в GitHub (`qbsm/avtofin.ru`) — SSH-ключ у юзера `promo`:
   ```bash
   GIT_SSH_COMMAND="ssh -i /home/promo/.ssh/id_ed25519" git push
   ```
4. Заливать на прод через scp **точечно**, по списку изменённых файлов. Не делать `rsync project/ → www/avtofin.org/` целиком.

### НЕ затирать на проде (прод-версии новее или специфичны)

- `config.php` — `form_secret` отличается
- `data/content/*.json` — структура локально может отставать
- `data/production/*-production.json` — редактируется на проде через админку
- `templates/parts/footer.twig` — на проде юр.данные из `data.legal`, локально хардкод
- `copy.php`, `read-json.php` — на проде уже `htmlspecialchars` (PHP 8.4)
- `data/docs/agree.pdf`
- `php-version.php`
- `*.bak-*`
- `logs/`

Перед scp всегда делать `diff -u local prod` чтобы понять, нет ли расхождений по контенту.

---

## Связанное

- Дев-копия и сборка: `../dev/` — gulpfile + webpack, `npm run build`
- Репозиторий: GitHub `qbsm/avtofin.ru`
- Прод: `https://avtofin.org` на хостинге Hostiman
- SEO-сниппет: общий гайд лежит в `service.avtodom-liauto/dev/docs/snippet-rules.md` (другой проект, но шаблон применим)
