# Правила организации и нейминга JSON в проекте

Соглашения по структуре файлов данных, именованию ключей и формату значений. Данные используются в Twig ([twig-rules.md](twig-rules.md)) и частично в JS; нейминг контента согласован с [html-rules.md](html-rules.md).

---

## 1. Структура каталогов и файлов

| Путь / каталог                        | Назначение                                                                 | Файлы                    |
| ------------------------------------- | -------------------------------------------------------------------------- | ------------------------ |
| **project/data/content/**             | Редактируемый источник контента (правится вручную)                         | `index.json`             |
| **project/data/production/**          | Рантайм-данные, читаются `index.php` при отрисовке (генерятся из `content`)| `index-production.json`  |
| **project/data/img/**                 | Изображения, сгруппированные по секциям/моделям                            | `data/img/ui/`, `data/img/models/`, `data/img/actions/` |

Сайт одностраничный: вся разметка — `index.json` → `index-production.json`. Имена файлов — **kebab-case**.

---

## 2. Именование ключей

### Общее правило

- **Имена секций** (поле `name` в массиве секций) — **kebab-case**, совпадают с именем папки компонента в `dev/src/components/*`: `header`, `intro`, `runline`, `actions`, `combomap`, `callback`, `footer`.
- **Поля контента** — единый набор слов (см. ниже), обычно **camelCase** для составных (`ctaText`, `emailRecipients`, `buttonText`) либо одно слово (`title`, `heading`, `items`).
- **id UI-элементов** внутри data — **camelCase**: `introSlider`, `burgerIcon`, `formCallbackPhone`.

Рекомендация по уровням:

| Уровень          | Стиль ключей            | Примеры                                                                |
| ---------------- | ----------------------- | ---------------------------------------------------------------------- |
| Имя секции       | kebab-case              | `header`, `intro`, `actions`, `combomap`, `footer`                     |
| Поля контента    | camelCase / одно слово  | `heading`, `title`, `subtitle`, `desc`, `items`, `ctaText`, `buttons`  |
| id для UI        | camelCase               | `introSlider`, `formCallbackPhone`                                     |
| Служебные/SEO    | camelCase               | `page`, `title`, `description`, `emailRecipients`                      |

### Единый набор слов для контента

Использовать согласованные имена (см. [html-rules.md](html-rules.md)):

| Ключ            | Назначение                                              |
| --------------- | ------------------------------------------------------- |
| **heading**     | Заголовок блока/секции                                  |
| **headline**    | Крупный слоган/строка                                   |
| **subheading**  | Подзаголовок секции                                     |
| **subtitle**    | Подзаголовок карточки/элемента                          |
| **title**       | Название, заголовок элемента (карточки, кнопки, ссылки) |
| **desc**        | Описание, поясняющий текст                              |
| **name**        | Имя секции / блока                                      |
| **label**       | Подпись к полю или элементу                             |
| **placeholder** | Плейсхолдер поля ввода                                  |
| **link**        | Ссылка (объект с title, href)                           |
| **content**     | Обёртка/текст основного контента                        |

---

## 3. Структура файла index.json

Файл `project/data/content/index.json` имеет вид:

```json
{
  "page": "index",
  "title": "...",
  "description": "...",
  "emailRecipients": [],
  "globals": { ... },
  "firstScreen": [ { "name": "header", ... }, { "name": "intro", ... } ],
  "secondaryScreen": [ { "name": "runline", ... }, ... ]
}
```

### Корневые поля

- **`page`** — идентификатор страницы (для маршрутизации `index.php`).
- **`title`** / **`description`** — SEO-метаданные страницы.
- **`emailRecipients`** — массив получателей форм (`form.php`).
- **`globals`** — site-wide данные (бренд, дилеры, общие тексты, контакты).
- **`firstScreen`** — массив секций первого экрана (критичные для LCP).
- **`secondaryScreen`** — массив остальных секций, рендерящихся ниже.

### Секция

Каждая секция — объект с полями напрямую (без обёртки `data`):

```json
{
  "name": "actions",
  "title": "Акции",
  "visible": true,
  "image": "data/img/actions/banner.webp",
  "heading": "...",
  "items": [...],
  "buttons": [...]
}
```

- **`name`** — в **kebab-case**, совпадает с именем папки компонента `dev/src/components/{name}/` и файла `{name}.twig`.
- **`visible`** — boolean, включать ли секцию в рендер.
- Остальные поля — контент секции (зависит от компонента).

---

## 4. Глобальные данные (`globals`)

Блок `globals` лежит внутри того же `index.json`. Содержит site-wide данные:

- **`brand`** — название бренда (строка).
- **`dealers`** — массив дилерских центров: `title`, `phone`, `address`, `worktime`, `geo`, `services`, `cover`, `messengers`.
- Прочие общие блоки (тексты форм, меню, футер) — ключи в **kebab-case**, совпадающие с именами компонентов (`form-callback`, `cookie-panel`).
- Пути к ресурсам — относительные: `data/img/...`, `data/img/ui/...`.

---

## 5. Формат и стиль записи

- **JSON без комментариев** — стандартный JSON не поддерживает комментарии; справочная информация — в гайдах.
- **Пробелы:** отступ 2 пробела для читаемости.
- **Строки:** двойные кавычки **`"`**; экранирование по стандарту JSON.
- **Числа и булевы:** без кавычек.
- **HTML в значениях** допускается (`<span class="nowrap">...</span>`, `<br>`), выводится через Twig-фильтр `| raw`.

---

## 6. Поток данных content → production

- `project/data/content/index.json` — источник правды, правится руками.
- `project/data/production/index-production.json` — потребляется `index.php`. Обновлять при изменении контента (в простом случае — ручная синхронизация).
- Не дублировать поля: если контент одинаковый — оба файла должны иметь одинаковые значения после правки.

---

## 7. Валидация и консистентность

- Имена секций в `firstScreen[].name` / `secondaryScreen[].name` должны совпадать с папкой компонента `dev/src/components/{name}/` и иметь `{name}.twig`.
- Ключи в `globals` для компонентов совпадают с именами компонентов (kebab-case): `global['form-callback']`, `global.dealers`.
- При добавлении новой секции: создать компонент в `dev/src/components/{name}/`, добавить объект секции в массив, запустить `npm run build`.

---

## Краткая шпаргалка

| Сущность           | Формат / правило                                                              |
| ------------------ | ----------------------------------------------------------------------------- |
| Имена файлов       | kebab-case (`index.json`, `index-production.json`)                            |
| Имя секции         | `name` в kebab-case, соответствие папке `components/{name}/` и файлу `{name}.twig` |
| Блоки в globals    | Ключи в kebab-case: `form-callback`, `cookie-panel`, `dealers`                |
| Поля контента      | heading, title, desc, label, placeholder, subheading, subtitle (единый набор) |
| id для UI          | В данных в camelCase: `introSlider`, `formCallbackPhone`                      |
| Структура страницы | `page` + `globals` + `firstScreen[]` + `secondaryScreen[]` (секции: `name`, `visible`, поля) |

---

## См. также

- [html-rules.md](html-rules.md) — нейминг классов, id, data-атрибутов.
- [twig-rules.md](twig-rules.md) — как данные используются в шаблонах.
