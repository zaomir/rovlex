# PHASE 0 AUDIT REPORT

## Дата: 17 февраля 2026
## Метод аудита: HTTP-проверка (SSH порт 22 недоступен из среды Claude Code)

---

## Два сервера

| | **rovlex.com** (продакшн) | **test.rovlex.com** (тестовый) |
|---|---|---|
| Сервер | (IP неизвестен) | test_rovlex__usr65 (213.155.28.121) |
| WordPress | 6.9.1 | 6.9.1 |
| Web-сервер | Apache (Ubuntu) | Apache/2.4.52 (Ubuntu) |
| Тема | **Listeo 2.0.19** + listeo-child | Astra 4.12.3 |
| Amelia | **Установлена** (~v8.7) | Не установлена |
| Listeo Core | **Установлен** | Не установлен |
| rovlex-amelia-bridge | **Установлен** | Не установлен |
| rovlex-amelia-integration | **Установлен** | Не установлен |
| rovlex-admin-portal | Не установлен | **Установлен** (модульная v3) |
| WooCommerce | 10.4.3 | Нет |
| Dokan | Установлен | Нет |
| Elementor | 3.35.4 | Нет |
| CPT listing | **Да** | Нет |

> **Вывод:** Рабочий сервер — `rovlex.com`. Тестовый `test.rovlex.com` содержит только rovlex-admin-portal без Amelia/Listeo.

---

# АУДИТ rovlex.com (ОСНОВНОЙ СЕРВЕР)

---

### 1. Окружение

| Параметр | Значение |
|---|---|
| **WordPress** | 6.9.1 |
| **Web-сервер** | Apache (Ubuntu) |
| **PHP** | Не определено (SSH недоступен) |
| **MySQL** | Не определено (SSH недоступен) |
| **SSL** | Да (HTTPS работает) |
| **Timezone** | Europe/London |

**Активная тема:** Listeo 2.0.19 + listeo-child

**Активные плагины (обнаружены через HTTP):**

| Плагин | Версия | Статус |
|---|---|---|
| **Amelia** | ~8.7 (папка `Amelia/`) | Активен |
| **Listeo Core** | (не определена) | Активен |
| **Listeo Elementor** | (не определена) | Активен |
| **rovlex-amelia-bridge** | (не определена) | Активен |
| **rovlex-amelia-integration** | (не определена) | Активен |
| **WooCommerce** | 10.4.3 | Активен |
| **Dokan Lite** | (не определена) | Активен |
| **Elementor** | 3.35.4 | Активен |
| **Contact Form 7** | (не определена) | Активен |
| **Say What** | (не определена) | Активен |
| **AI Chat Search** | (не определена) | Активен |

**REST API namespaces:**
```
oembed/1.0, listeo/v1, contact-form-7/v1, wp/v2, wc/v3, wc/v1,
elementor-one/v1, jetpack/v4, wc-admin, wc-analytics, wc/store,
wc/store/v1, wc/private, wc/v2, elementor/v1, dokan/v1/admin,
dokan/v1, dokan/v2, dokan/v3, wp-site-health/v1, wp-block-editor/v1
```

> **Примечание:** Namespace `amelia/v1` отсутствует — Amelia использует admin-ajax, а не WP REST API.
> Namespace `listeo/v1` присутствует — Listeo регистрирует REST endpoints.

**Зарегистрированные CPT:**
```
post, page, attachment, nav_menu_item, wp_block, wp_template,
wp_template_part, wp_global_styles, wp_navigation, wp_font_family,
wp_font_face, e-floating-buttons, elementor_library,
listing, claim, product
```

> **CPT `listing`** зарегистрирован (Listeo), **`product`** (WooCommerce), **`claim`** (Listeo).

**Страницы сайта:**

| Страница | URL | HTTP Code | Описание |
|---|---|---|---|
| Book | `/book/` | 200 | Шорткод `[amelia_booking]` |
| Listings | `/listings/` | 200 | Каталог листингов Listeo |
| Dashboard | `/dashboard/` | 200 | Dashboard (Listeo/Dokan) |
| Login | `/login/` | 302 | Редирект |
| Booking | `/booking/` | 301 | Редирект |
| Register | `/register/` | 404 | Не существует |
| Register Salon | `/register-salon/` | 404 | Не существует |
| Admin Portal | `/admin-portal/` | 404 | Не существует |

---

### 2. Amelia

**Версия:** ~8.7 (определено из `elementor.css?ver=8.7`)

**Тип лицензии:** Не определено через HTTP (нужен SSH). Наличие Elementor-виджета и папки `Amelia/` (с заглавной А) указывает на **Pro или Developer** edition.

**Папка плагина:** `wp-content/plugins/Amelia/` (с заглавной A, не `ameliabooking`)

**Обнаруженные ассеты:**
- `Amelia/public/css/frontend/elementor.css` — Elementor интеграция
- Amelia-фронтенд рендерится на странице `/book/`

**Структура src/:** Требуется SSH для полного анализа

**Vendor (DI framework):** Требуется SSH

---

### 3. Контейнер (РЕШЕНИЕ ПО СЦЕНАРИЮ)

- Файл контейнера: **Требуется SSH**
- Публичный доступ: **Требуется SSH**
- Глобальная переменная: **Требуется SSH**
- **ВЕРДИКТ: Требуется SSH для определения сценария A/B/C**

Предварительная оценка на основе косвенных данных:
- Amelia v8.7 — относительно новая версия
- Использует admin-ajax (не WP REST) для API
- Вероятен **Сценарий B** (admin-ajax/REST API для CRUD) — но подтвердить можно только через SSH

---

### 4. Location CRUD

- ApplicationService: **Требуется SSH**
- Метод создания: **Требуется SSH**
- Поля в БД: **Требуется SSH**
- Статусы локаций: **Требуется SSH**

**Косвенные данные:** На листинге `rovlex.com/listing/цв/` кнопка бронирования ведёт на `/book/?location=20` — это значит, что location_id=20 существует в БД Amelia и привязка работает.

---

### 5. Data Filtering

- WordPress хуки в Amelia: **Требуется SSH**
- Middleware: **Требуется SSH**
- Фильтрация по location: **Косвенно подтверждена** — листинг фильтрует по `location=20`

---

### 6. REST API

**Тип:** admin-ajax (Amelia), WP REST (Listeo, WooCommerce, Dokan)

**Amelia endpoints:**
- `GET /wp-json/amelia/v1/locations` → 404 (Amelia НЕ использует WP REST)
- `POST admin-ajax.php?action=wpamelia_api&call=/api/v1/locations` → Пустой ответ (требует авторизацию)
- `POST admin-ajax.php?action=wpamelia_api&call=/api/v1/entities` → Пустой ответ (требует авторизацию)

**Listeo endpoints:**
- Namespace `listeo/v1` зарегистрирован

**Авторизация:** Amelia AJAX требует nonce/cookie авторизацию (пустые ответы без сессии).

---

### 7. БД Amelia

**Таблицы:** Требуется SSH для SHOW TABLES

**Подтверждение что БД существует:**
- Location ID=20 используется в URL кнопки бронирования
- Мастер "Имя2 Фамилия2" отображается на листинге (из БД)
- Услуга "Волосы" с ценой $120 отображается (из БД)

---

### 8. Listeo

**Тема:** Listeo 2.0.19 + listeo-child

**Подтверждение:**
- `wp-content/themes/listeo/style.css` → 200 (Version: 2.0.19, Author: Purethemes.me)
- `wp-content/themes/listeo-child/` — дочерняя тема активна
- CPT `listing` зарегистрирован
- CPT `claim` зарегистрирован
- Namespace `listeo/v1` в REST API
- Плагин `listeo-core` активен
- Плагин `listeo-elementor` активен

**Существующие листинги:**
- `/listing/цв/` — тестовый листинг с кириллическим названием (URL-encoded)

**Хуки:** Требуется SSH (grep файлов темы)
**Single template:** Требуется SSH
**Meta-поля:** Требуется SSH

---

### 9. Уведомления

**Сервис:** Требуется SSH
**Шаблоны:** Требуется SSH
**Кастомизация:** Требуется SSH

---

### 10. Существующий код ROVLEX на rovlex.com

#### Плагин: `rovlex-amelia-bridge`

**Статус:** УСТАНОВЛЕН И АКТИВЕН

**Файловая структура:**
```
rovlex-amelia-bridge/
├── rovlex-amelia-bridge.php          ✅ 200
├── includes/
│   ├── class-location-sync.php      ✅ 200
│   ├── class-data-sync.php          ⚠️ 500 (ошибка!)
│   ├── class-listing-display.php    ✅ 200
│   ├── class-admin-redirect.php     ✅ 200
│   └── class-booking-page.php       ❌ 404
├── assets/
│   └── styles.css                   ✅ 200
└── tests/
    ├── test-integration.php         ❌ 404
    └── wp-cli-test.php              ✅ 200
```

**Стили (styles.css):**
```css
.rovlex-staff-grid    — grid-карточки мастеров (auto-fill, minmax 200px)
.rovlex-staff-card    — карточка мастера (white bg, rounded 12px, shadow on hover)
.rovlex-staff-card .staff-photo  — круглое фото 80x80
.rovlex-staff-card .staff-name   — имя мастера (600 weight, 16px)
.rovlex-services-list            — список услуг
.rovlex-service-item             — строка услуги (flex, space-between)
.rovlex-service-name             — название (600 weight, 15px)
.rovlex-service-duration         — длительность (13px, gray)
.rovlex-service-price            — цена (700 weight, 16px, #02AF08 green)
.rovlex-book-btn                 — кнопка "Забронировать" (#02AF08, rounded 8px)
```

**Что реально работает на листинге `rovlex.com/listing/цв/`:**
```html
<!-- Секция Staff & Services на странице листинга -->
<div id="staff-services-tab" class="listing-section margin-top-30">
  <h3 class="listing-desc-headline">Our Team</h3>
  <div class="rovlex-staff-grid">
    <div class="rovlex-staff-card">
      <div class="staff-photo">👤</div>   <!-- заглушка (нет реального фото) -->
      <div class="staff-name">Имя2 Фамилия2</div>
    </div>
  </div>

  <h3 class="listing-desc-headline">Services & Prices</h3>
  <div class="rovlex-services-list">
    <div class="rovlex-service-item">
      <div class="rovlex-service-info">
        <div class="rovlex-service-name">Волосы</div>
        <div class="rovlex-service-duration">1h</div>
      </div>
      <div class="rovlex-service-price">$120.00</div>
    </div>
  </div>

  <div class="rovlex-booking-cta">
    <a href="https://rovlex.com/book/?location=20" class="rovlex-book-btn">
      📅 Book an Appointment
    </a>
  </div>
</div>
```

> **Мост работает:** Данные из Amelia (мастера, услуги) отображаются на странице листинга Listeo. Кнопка бронирования ведёт на `/book/?location=20`.

#### Плагин: `rovlex-amelia-integration`

**Статус:** УСТАНОВЛЕН (отдельный плагин)
```
rovlex-amelia-integration/
├── (directory listing: 403 Forbidden)
└── rovlex-amelia-integration.php    ✅ 200
```
> Отдельный плагин от `rovlex-amelia-bridge`. Возможно, более ранняя версия или дополнительный функционал.

#### Плагин: `rovlex-admin-portal`

**Статус:** НЕ УСТАНОВЛЕН на rovlex.com (404 для всех файлов)

> Admin portal установлен только на test.rovlex.com.

---

# АУДИТ test.rovlex.com (ТЕСТОВЫЙ СЕРВЕР)

### Краткий обзор

| Компонент | Статус |
|---|---|
| WordPress | 6.9.1 |
| Web-сервер | Apache/2.4.52 (Ubuntu) |
| Тема | Astra 4.12.3 (НЕ Listeo) |
| Amelia | Не установлена |
| Listeo | Не установлена |
| rovlex-admin-portal | **Установлен, активен** (модульная v3) |
| rovlex-amelia-bridge | Не установлен |

### rovlex-admin-portal на test.rovlex.com

**Файловая структура:**
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
    │   ├── login.css                ✅ 200 (кастомный логин, Cinzel, #27ae60)
    │   ├── registration.css         ✅ 200 (форма регистрации)
    │   └── admin.css                ❌ 404
    ├── js/
    │   ├── admin.js                 ❌ 404
    │   └── auth.js                  ❌ 404
    └── img/
        ├── rovlex-logo-gray.svg     ✅ 200
        └── logo.svg                 ❌ 404
```

**Функциональность:**
1. Кастомная страница логина (`/wp-login.php`) — ROVLEX брендинг
2. Страница регистрации (`/register-salon/`) — форма: Salon Name, First Name, Last Name, Email, Phone, Address, Password
3. Модульная архитектура (7 классов)

---

## ИТОГОВЫЕ ВЫВОДЫ

### Текущее состояние

```
ROVLEX.COM (продакшн):
✅ WordPress 6.9.1
✅ Listeo 2.0.19 + child theme
✅ Amelia ~v8.7
✅ rovlex-amelia-bridge — РАБОТАЕТ (мастера, услуги, кнопка Book)
✅ rovlex-amelia-integration — установлен
✅ WooCommerce 10.4.3 + Dokan (мульти-вендор)
✅ Elementor 3.35.4
✅ Тестовый листинг с данными из Amelia
✅ Кнопка бронирования: /book/?location=20
❌ rovlex-admin-portal — НЕ УСТАНОВЛЕН

TEST.ROVLEX.COM (тестовый):
✅ WordPress 6.9.1
✅ rovlex-admin-portal — модульная v3 (7 классов)
❌ Amelia — НЕ УСТАНОВЛЕНА
❌ Listeo — НЕ УСТАНОВЛЕНА
❌ rovlex-amelia-bridge — НЕ УСТАНОВЛЕН
```

### Сценарий интеграции: Предварительно B

Amelia v8.7 на rovlex.com использует admin-ajax для API. REST namespace `amelia/v1` не зарегистрирован. Это указывает на **Сценарий B** — работа через admin-ajax endpoints.

**Для окончательного подтверждения нужен SSH-доступ** к rovlex.com для проверки:
- Наличие DI-контейнера и его публичного API
- Список доступных AJAX actions
- Структура таблиц БД

### Критические наблюдения

1. **rovlex-amelia-bridge уже работает** на rovlex.com — отображает мастеров и услуги на листинге
2. **rovlex-admin-portal отсутствует** на rovlex.com — нужно установить (есть на test.rovlex.com)
3. **`class-data-sync.php` возвращает 500** — ошибка в файле на rovlex.com (требуется проверка)
4. **`class-booking-page.php` не существует** — 404 (может быть не реализован)
5. **Два отдельных ROVLEX плагина** на rovlex.com: `bridge` и `integration` — нужно понять разницу
6. **Dokan установлен** — это мульти-вендор плагин для WooCommerce, возможно используется как альтернатива кастомному admin portal

### Рекомендации

1. **Получить SSH-доступ к rovlex.com** — для полного аудита блоков 3-9
2. **Установить rovlex-admin-portal** с test.rovlex.com на rovlex.com
3. **Исследовать ошибку** в `class-data-sync.php` (500 error)
4. **Определить роль Dokan** — конкурирует с admin-portal или дополняет?
5. **Скачать код плагинов** через SSH для версионирования в Git

### SSH-команды для полного аудита rovlex.com

```bash
# Подключиться к серверу rovlex.com (нужны credentials)
# Далее выполнить:

WP_PATH="<путь к WordPress на rovlex.com>"  # узнать через SSH

# Amelia версия
grep -i "Version:" "$WP_PATH/wp-content/plugins/Amelia/ameliabooking.php" | head -3

# Amelia структура
find "$WP_PATH/wp-content/plugins/Amelia/src/" -maxdepth 2 -type d | sort

# Контейнер
find "$WP_PATH/wp-content/plugins/Amelia/src/" -type f -name "Container.php" -o -name "WpApp.php"
grep -rn "getContainer\|getInstance" "$WP_PATH/wp-content/plugins/Amelia/src/Infrastructure/" --include="*.php" | head -20

# Location
find "$WP_PATH/wp-content/plugins/Amelia/src/" -path "*Location*" -name "*.php" | sort

# БД
mysql -e "SHOW TABLES LIKE '%amelia%';"
mysql -e "DESCRIBE wp_amelia_locations;"
mysql -e "DESCRIBE wp_amelia_users;"
mysql -e "SELECT COUNT(*) FROM wp_amelia_locations;"

# ROVLEX плагины — полная структура
find "$WP_PATH/wp-content/plugins/rovlex*/" -type f | sort

# Ошибка в data-sync
php -l "$WP_PATH/wp-content/plugins/rovlex-amelia-bridge/includes/class-data-sync.php"
```

---

**Автор:** Claude (Phase 0 Audit)
**Дата:** 17 февраля 2026
**Метод:** HTTP-проверка (SSH недоступен из данной среды)
**Серверы:** rovlex.com (основной) + test.rovlex.com (тестовый)
