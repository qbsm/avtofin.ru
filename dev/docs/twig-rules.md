# Правила организации и нейминга Twig в проекте

Соглашения по структуре шаблонов, именованию переменных, включениям и блокам. Согласовано с [html-rules.md](html-rules.md), [css-rules.md](css-rules.md) и данными из JSON ([json-rules.md](json-rules.md)).

---

## 1. Структура каталогов и файлов

| Каталог         | Назначение                                                   | Примеры файлов                                                      |
| --------------- | ------------------------------------------------------------ | ------------------------------------------------------------------- |
| **templates/**  | Корень шаблонов                                              | —                                                                   |
| **base.twig**   | Базовый layout: `<html>`, `<head>`, `<body>`, блок `content` | —                                                                   |
| **pages/**      | Страницы: наследуют base, подключают секции                  | `page.twig`                                                         |
| **sections/**   | Крупные блоки страницы (header, footer, intro, content)      | `header.twig`, `footer.twig`, `intro.twig`, `content.twig`          |
| **components/** | Переиспользуемые блоки (кнопка, форма, карточка, heading)    | `form-callback.twig`, `button.twig`, `heading.twig`, `picture.twig` |
| **errors/**     | Страницы ошибок                                              | `404.twig`, `500.twig`, `http.twig`                                 |

**Правило:** имя файла секции/компонента в **kebab-case** и совпадает с именем из JSON и с блоком в CSS: секция `form-container` → `sections/form-container.twig` (если есть); компонент `form-callback` → `components/form-callback.twig`.

---

## 2. Подключение секций и компонентов

### Страница

- Страница наследует **`base.twig`** и в блоке `content` перебирает массив **`sections`** из данных страницы.
- Подключение секции: **`{% include 'sections/' ~ section.name ~ '.twig' ignore missing with {'data': section.data} %}`**.
- Имя секции в `section.name` — **kebab-case**, соответствует имени файла без расширения: `header`, `intro`, `form-container`, `footer`.

### Включение компонентов

- **`{% include 'components/имя-компонента.twig' with { ... } %}`** — с передачей переменных.
- Передавать только нужные данные: например, `with { items: global.nav.items }`, `with {'item': slide.heading}`, `with {'data': section }`.
- Имя компонента — **kebab-case**: `form-callback.twig`, `burger-icon.twig`, `card.twig`.

---

## 3. Переменные окружения и глобальные данные

Имена переменных, доступных в шаблонах (задаются бэкендом или сборкой):

| Переменная          | Назначение                                                                   |
| ------------------- | ---------------------------------------------------------------------------- |
| **root**            | Базовый URL сайта (префикс для путей к ассетам)                              |
| **pageData**        | Данные страницы (корень `index-production.json`: `page`, `title`, `description`, `globals`, `firstScreen[]`, `secondaryScreen[]`) |
| **firstScreen**     | Массив секций первого экрана (критичные для LCP)                             |
| **secondaryScreen** | Массив остальных секций (рендерятся ниже)                                    |
| **globals**         | Глобальные данные (бренд, дилеры, тексты форм) — ключи в kebab-case для блоков |
| **utm_link(url)**   | Функция, достраивающая UTM-параметры к ссылке                                |

В шаблоне обращение к глобальным блокам: **`globals['form-callback']`**, **`globals.dealers`** — ключи в **kebab-case** для имён блоков.

---

## 4. Локальные переменные в шаблонах

- **`{% set varName = value %}`** — имя переменной в **camelCase** или **snake_case** в зависимости от контекста; для временных/служебных — допустим префикс **`_`**: `_og_title`, `_path_segment`, `_base_url_trimmed`.
- Внутри секции контент приходит в **`data`**: `data.heading`, `data.items`, `data.buttons` и т.д. Структура `data` — это объект секции из JSON (поля заданы напрямую, без вложенного ключа `data`; см. [json-rules.md](json-rules.md)).

---

## 5. Классы и идентификаторы в разметке

- Классы и id задаются по правилам [html-rules.md](html-rules.md): BEM-подобная схема, **kebab-case** для классов, **camelCase** для id.
- Динамические классы: **`class="header js-header{{ data.variant == 'dark' ? ' header-dark' : '' }}"`** — без лишних пробелов внутри строки.
- Уникальный id формы: **`{% set formId = 'formCallback' ~ random(1000000) %}`**, в полях **`id="{{ formId }}Phone"`** — camelCase составного id.

---

## 6. Вывод данных и экранирование

- Вывод текста/атрибутов: **`{{ variable }}`** — автоэкранирование; для HTML-фрагментов из доверенных данных — **`{{ content|raw }}`** только там, где нужно.
- В атрибутах и JS-строках: **`{{ value|e('js') }}`** или **`|e('html_attr')`** по необходимости, чтобы исключить инъекции.
- Проверка наличия: **`{% if section.visible | default(true) %}`, `data.slider.items is defined and data.slider.items is not empty`** — избегать обращений к несуществующим ключам.

---

## 7. Комментарии

- **`{# комментарий #}`** — для пояснений в шаблоне; кратко, по-русски или по-английски.
- Блоки логики (расчёт canonical, alternate) можно помечать комментарием в начале.

---

## 8. Связь с JSON и компонентами

- Имена секций в **`firstScreen[].name`** / **`secondaryScreen[].name`** в JSON — **kebab-case**, те же, что и имя папки компонента в `dev/src/components/{name}/` и файла `{name}.twig`.
- Ключи в **`globals`** для компонентов — **kebab-case**: `form-callback`, `cookie-panel`, `dealers`.
- Единый набор слов для контента (heading, title, desc, label, placeholder и т.д.) — см. [html-rules.md](html-rules.md); в Twig использовать те же ключи: `data.heading`, `globals['form-callback'].label.phone`.

---

## Краткая шпаргалка

| Сущность                 | Формат / правило                                                         |
| ------------------------ | ------------------------------------------------------------------------ | ---------------------------- |
| Файлы секций/компонентов | kebab-case, соответствие имени в JSON и CSS                              |
| Имя секции               | section.name в kebab-case                                                |
| Подключение секции       | include 'components/' ~ section.name ~ '/' ~ section.name ~ '.twig' with {'data': section} |
| Глобальные блоки         | globals['form-callback'], globals.dealers — ключи kebab-case             |
| Данные секции            | переменная data (data.heading, data.items, …) — поля из объекта секции  |
| Временные переменные     | {% set _name = ... %} для служебных                                      |
| id в разметке            | camelCase; форма: formId ~ 'Phone', formId ~ 'Name'                      |
| Экранирование            | {{ }} по умолчанию; raw только для доверенного HTML;                     | e('js') в атрибутах/скриптах |
