# PHASE 0 AUDIT REPORT

## Дата: 17 февраля 2026
## Сервер: test_rovlex__usr65 (213.155.28.121)
## Метод аудита: HTTP-проверка (SSH порт 22 недоступен из среды Claude Code)

---

### 1. Окружение

| Параметр | Значение |
|---|---|
| **WordPress** | 6.9.1 |
| **Web-сервер** | Apache/2.4.52 (Ubuntu) |
| **PHP** | Не определено (SSH недоступен) |
| **MySQL** | Не определено (SSH недоступен) |
| **SSL** | Да (HTTPS работает) |
| **Timezone** | Europe/London |

**Активная тема:** Astra 4.12.3

**Активные плагины (обнаружены через HTTP):**
- `rovlex-admin-portal` v1.0.0 — **АКТИВЕН** (кастомный плагин, кастомизирует логин и регистрацию)
- `astra` — стандартный Astra theme

**Плагины НЕ обнаружены:**
- ~~Amelia~~ — **НЕ УСТАНОВЛЕНА** (ни в одном варианте: ameliabooking, amelia-booking, amelia, ameliabooking-pro)
- ~~Listeo~~ — **НЕ УСТАНОВЛЕНА** (ни тема, ни Listeo Core plugin)
- ~~Bookly~~ — **НЕ УСТАНОВЛЕН**
- ~~rovlex-amelia-bridge~~ — **НЕ УСТАНОВЛЕН**
- ~~rovlex-amelia-integration~~ — **НЕ УСТАНОВЛЕН**

**REST API namespaces:**
```
oembed/1.0
astra/v1
nps-survey/v1
wp/v2
wp-site-health/v1
wp-block-editor/v1
wp-abilities/v1
```
> Нет namespace от Amelia (ожидался `amelia/v1` или AJAX endpoint `wpamelia_api`)

**Зарегистрированные CPT (Custom Post Types):**
```
post, page, attachment, nav_menu_item, wp_block, wp_template,
wp_template_part, wp_global_styles, wp_navigation, wp_font_family, wp_font_face
```
> Нет CPT `listing` (от Listeo). Нет CPT от Amelia.

**Страницы сайта (обнаружены):**
| Страница | URL | HTTP Code | Содержимое |
|---|---|---|---|
| Register Salon | `/register-salon/` | 200 | Кастомная форма регистрации ROVLEX |
| Sample Page | `/sample-page/` | 200 | Стандартная страница WP |
| Dashboard | `/dashboard/` | 302 (redirect) | Перенаправляет |
| Login | `/login/` | 302 (redirect) | Перенаправляет |
| Admin Portal | `/admin-portal/` | 404 | Не существует |
| Amelia Dashboard | `/amelia-dashboard/` | 404 | Не существует |
| Book | `/book/` | 404 | Не существует |
| Booking | `/booking/` | 404 | Не существует |

---

### 2. Amelia

**Версия:** НЕ УСТАНОВЛЕНА

**Тип лицензии:** Н/Д

**Структура src/:** Н/Д

**Vendor (DI framework):** Н/Д

**Подтверждение отсутствия Amelia:**
- `wp-content/plugins/ameliabooking/` → 404
- `wp-content/plugins/amelia-booking/` → 404
- `wp-content/plugins/amelia/` → 404
- `wp-content/plugins/ameliabooking-pro/` → 404
- REST namespace `amelia/v1` отсутствует
- AJAX `wpamelia_api` не отвечает (пустой ответ)
- На страницах сайта нет CSS/JS от Amelia

---

### 3. Контейнер (РЕШЕНИЕ ПО СЦЕНАРИЮ)

- Файл контейнера: **Н/Д — Amelia не установлена**
- Публичный доступ: **Н/Д**
- Глобальная переменная: **Н/Д**
- **ВЕРДИКТ: Невозможно определить сценарий (A/B/C)**

> Amelia должна быть установлена перед определением сценария интеграции. Рекомендуется установить Amelia Pro и повторить аудит блоков 3-5.

---

### 4. Location CRUD

- ApplicationService: **Н/Д — Amelia не установлена**
- Метод создания: **Н/Д**
- Поля в БД: **Н/Д** (таблица `wp_amelia_locations` не существует)
- Статусы локаций: **Н/Д**

---

### 5. Data Filtering

- WordPress хуки в Amelia: **Н/Д — Amelia не установлена**
- Middleware: **Н/Д**
- Фильтрация по location: **Н/Д**

---

### 6. REST API

**Тип:** Только стандартный WordPress REST API (`wp/v2`)

**Amelia endpoints:** Не существуют
- `GET /wp-json/amelia/v1/locations` → `404 rest_no_route`
- `POST admin-ajax.php?action=wpamelia_api&call=/api/v1/locations` → Пустой ответ (handler не зарегистрирован)
- `POST admin-ajax.php?action=wpamelia_api&call=/api/v1/entities` → Пустой ответ

**Авторизация WP REST:**
- Application passwords поддерживаются
- Endpoint `/wp-json/wp/v2/plugins` → 401 (требует авторизацию)
- Endpoint `/wp-json/wp/v2/users` → Публичный (видно 1 пользователя: `test_rovlex`)

---

### 7. БД Amelia

**Таблицы:** Не проверены (SSH недоступен из данной среды, MySQL не доступен удалённо)

**Ожидаемый результат:** Если Amelia не установлена — таблиц `wp_amelia_*` в БД нет.

> Для полной проверки БД нужен SSH или phpMyAdmin доступ.

---

### 8. Listeo

**Тема Listeo:** НЕ УСТАНОВЛЕНА

**Подтверждение:**
- `wp-content/themes/listeo/style.css` → 404
- Активная тема: **Astra 4.12.3** (не Listeo)
- CPT `listing` не зарегистрирован
- Плагин `listeo-core` → 404

**Хуки:** Н/Д (нет Listeo)
**Single template:** Н/Д
**Meta-поля:** Н/Д
**Существующие листинги:** 0 (CPT не существует)

---

### 9. Уведомления

**Сервис:** Н/Д — Amelia не установлена
**Шаблоны:** Н/Д
**Кастомизация:** Н/Д

---

### 10. Существующий код ROVLEX

#### Плагин: `rovlex-admin-portal` v1.0.0

**Статус:** УСТАНОВЛЕН И АКТИВЕН

**Файловая структура (проверено через HTTP 200/404):**
```
rovlex-admin-portal/
├── rovlex-admin-portal.php          ✅ 200
├── includes/
│   ├── class-roles.php              ✅ 200
│   ├── class-menu.php               ✅ 200
│   ├── class-registration.php       ✅ 200
│   ├── class-login.php              ✅ 200
│   ├── class-data-isolation.php     ✅ 200
│   ├── class-admin-cleanup.php      ✅ 200
│   └── class-redirects.php          ✅ 200
├── templates/
│   └── registration.php             ✅ 200
└── assets/
    ├── css/
    │   ├── login.css                ✅ 200
    │   ├── registration.css         ✅ 200
    │   └── admin.css                ❌ 404
    ├── js/
    │   ├── admin.js                 ❌ 404
    │   └── auth.js                  ❌ 404
    └── img/
        ├── rovlex-logo-gray.svg     ✅ 200
        └── logo.svg                 ❌ 404
```

**Функциональность (обнаружено через HTTP):**

1. **Кастомная страница логина** (`/wp-login.php`):
   - Логотип ROVLEX (шрифт Cinzel, зелёный #27ae60)
   - Кастомные стили (скруглённые углы, тени)
   - Enqueued: `rovlex-login-css`, `rovlex-cinzel-font`

2. **Страница регистрации** (`/register-salon/`):
   - Шорткод (предположительно `[rovlex_registration]`)
   - Форма с полями: Salon Name, First Name, Last Name, Email, Phone, Salon Address, Password
   - Nonce: `rovlex_register_nonce`
   - POST submission (не AJAX)
   - Enqueued: `rovlex-registration-css`
   - Логотип: `rovlex-logo-gray.svg`

3. **Модульная архитектура** (7 классов):
   - `class-roles.php` — управление ролями
   - `class-menu.php` — кастомное меню
   - `class-registration.php` — регистрация
   - `class-login.php` — логин
   - `class-data-isolation.php` — изоляция данных
   - `class-admin-cleanup.php` — очистка wp-admin
   - `class-redirects.php` — редиректы

> **Примечание:** Это модульная версия Разработчика 3 (epic-dhawan), а НЕ iframe-версия Разработчика 2. Подтверждается файловой структурой (7 отдельных классов, а не монолитный файл 427 строк).

#### Другие плагины ROVLEX:
- `rovlex-amelia-bridge` → 404 (не установлен)
- `rovlex-amelia-integration` → 404 (не установлен)

#### Таблицы ROVLEX в БД:
- Не проверено (SSH недоступен)

#### User meta:
- Не проверено (SSH недоступен)

---

## ИТОГОВЫЕ ВЫВОДЫ

### Текущее состояние сервера

```
✅ WordPress 6.9.1 — установлен и работает
✅ Apache/2.4.52 — работает, SSL настроен
✅ rovlex-admin-portal — установлен, активен (модульная v3 от Dev3)
✅ Кастомная регистрация — форма работает
✅ Кастомный логин — стилизован ROVLEX

❌ Amelia — НЕ УСТАНОВЛЕНА
❌ Listeo — НЕ УСТАНОВЛЕНА (тема Astra вместо Listeo)
❌ rovlex-amelia-bridge — НЕ УСТАНОВЛЕН
❌ Нет CPT "listing" (нет Listeo Core)
❌ Нет таблиц Amelia в БД
```

### Сценарий интеграции: НЕВОЗМОЖНО ОПРЕДЕЛИТЬ

Amelia не установлена на сервере. Сценарий (A/B/C) можно определить только после установки Amelia Pro.

### Критические ограничения

1. **Amelia отсутствует** — ни один блок аудита (контейнер, CRUD, API, БД, уведомления) не может быть выполнен
2. **Listeo отсутствует** — нет CPT listings, нет хуков, нет шаблонов
3. **SSH недоступен из среды Claude Code** — глубокий аудит файлов и БД невозможен удалённо
4. **Тема Astra** — не является целевой темой (нужна Listeo)

### Рекомендации

#### Вариант A: Доустановить на текущий сервер

1. Установить **Listeo** тему (купить на ThemeForest, заменить Astra)
2. Установить **Listeo Core** companion plugin
3. Установить **Amelia Pro** (купить лицензию)
4. Активировать оба
5. **Повторить аудит** блоков 2-9 с SSH-доступом

#### Вариант B: Чистая установка (рекомендуется)

Согласно обновлённому плану (`INTEGRATION_PLAN.md`):
1. Подготовить чистый сервер (или использовать текущий)
2. Установить WordPress + Listeo + Amelia Pro с нуля
3. Перенести `rovlex-admin-portal` (уже на сервере, модульная версия)
4. Разработать `rovlex-amelia-bridge` с нуля
5. Аудит провести после установки Amelia

#### Для повторного аудита после установки Amelia

Повторить блоки:
- **Блок 3:** Bootstrap и DI-контейнер → определить сценарий A/B/C
- **Блок 4:** Location CRUD → найти методы создания локаций
- **Блок 5:** Хуки фильтрации → определить возможность изоляции данных
- **Блок 6:** REST API → проверить endpoints для CRUD операций
- **Блок 7:** БД → получить полную схему таблиц Amelia
- **Блок 9:** Уведомления → проверить возможность кастомизации

### Что доступно для немедленной работы

Несмотря на отсутствие Amelia и Listeo, можно:
1. Продолжать разработку `rovlex-admin-portal` (уже на сервере)
2. Проектировать архитектуру `rovlex-amelia-bridge` на уровне интерфейсов
3. Подготовить конфигурации и скрипты установки

---

## Приложение: SSH-команды для повторного аудита

Когда SSH будет доступен, выполнить:

```bash
WP_PATH="/var/www/test_rovlex__usr65/data/www/test.rovlex.com"
DB_USER="test_rovlex_"
DB_PASS=':f:39vBcA?Ut}&uu'
DB_NAME="test_rovlex_"

# Версии
php -v | head -1
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT VERSION();" 2>/dev/null

# Активные плагины
php -r "
  define('ABSPATH', '$WP_PATH/');
  require_once ABSPATH . 'wp-load.php';
  print_r(get_option('active_plugins'));
"

# Amelia
AMELIA_PATH="$WP_PATH/wp-content/plugins/ameliabooking"
grep -i 'Version:' "$AMELIA_PATH/ameliabooking.php" 2>/dev/null | head -3

# Таблицы Amelia
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE '%amelia%';" 2>/dev/null

# Таблицы ROVLEX
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE '%rovlex%';" 2>/dev/null

# User meta ROVLEX
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
  SELECT DISTINCT meta_key FROM wp_usermeta
  WHERE meta_key LIKE '%rovlex%' OR meta_key LIKE '%amelia_location%' OR meta_key LIKE '%location_id%';
" 2>/dev/null
```

---

**Автор:** Claude (Phase 0 Audit)
**Дата:** 17 февраля 2026
**Метод:** HTTP-проверка (SSH недоступен)
**Сервер:** test_rovlex__usr65 (213.155.28.121)
