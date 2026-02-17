# ROVLEX — План с нуля: Чистый WordPress + Listeo + Amelia

**Дата:** 17 февраля 2026
**Цель:** Развернуть чистую систему с нуля и написать оба кастомных плагина

---

## Бизнес-цель

Маркетплейс салонов красоты (позже — handyman), где:

1. **Владелец салона** регистрируется → создаёт листинг (описание, фото, адрес) → получает публичную страницу салона
2. **Владелец** через админ-портал добавляет мастеров и услуги (с ценой и длительностью)
3. **Клиент** находит салон на карте → переходит → видит мастеров и услуги → бронирует

---

## Целевая архитектура

```
┌──────────────────────────────────────────────────┐
│              WordPress + Listeo Theme             │
│                                                   │
│  ┌─────────────────────┐  ┌────────────────────┐  │
│  │ rovlex-amelia-bridge │  │ rovlex-admin-portal│  │
│  │   (Кастомный #1)    │  │   (Кастомный #2)   │  │
│  │                     │  │                    │  │
│  │ • Листинг→Локация   │  │ • Роль rovlex_owner│  │
│  │ • Крон-синхронизация │  │ • Кастомное меню   │  │
│  │ • Book Now кнопка   │  │ • Изоляция данных  │  │
│  │ • Staff/Services    │  │ • Логин/Регистрация│  │
│  │   на листинге       │  │ • Скрытие wp-admin │  │
│  └─────────┬───────────┘  └──────────┬─────────┘  │
│            │                         │             │
│            ▼                         ▼             │
│  ┌───────────────────────────────────────────────┐ │
│  │           Amelia Booking Plugin (Pro)          │ │
│  │  Locations │ Employees │ Services │ Bookings  │ │
│  └───────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────┘
```

### Два кастомных плагина

| Плагин | Отвечает за |
|---|---|
| **rovlex-amelia-bridge** | Мост Listeo ↔ Amelia: автосоздание локации при публикации листинга, синхронизация мастеров/услуг на листинг (крон), кнопка "Забронировать", отображение на карточке |
| **rovlex-admin-portal** | Портал владельца: кастомная роль, меню, изоляция данных (видит только своё), кастомный логин/регистрация, скрытие стандартного wp-admin |

---

## Фаза 0: Подготовка сервера

### 0.1. Требования к серверу

- **ОС:** Ubuntu 22.04 LTS
- **Web-сервер:** Nginx или Apache
- **PHP:** 8.0+ (рекомендуется 8.1)
- **MySQL:** 8.0+ или MariaDB 10.6+
- **RAM:** минимум 2 GB (рекомендуется 4 GB)
- **Диск:** минимум 20 GB SSD
- **SSL:** Let's Encrypt (обязателен для Stripe)

### 0.2. Установка стека

```bash
# Обновление системы
apt update && apt upgrade -y

# Установка Nginx + PHP + MySQL
apt install nginx mysql-server php8.1 php8.1-fpm php8.1-mysql \
  php8.1-curl php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip \
  php8.1-intl php8.1-imagick -y

# SSL
apt install certbot python3-certbot-nginx -y
certbot --nginx -d test.rovlex.com
```

### 0.3. Настройка MySQL

```sql
CREATE DATABASE rovlex_wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rovlex_user'@'localhost' IDENTIFIED BY '<STRONG_PASSWORD>';
GRANT ALL PRIVILEGES ON rovlex_wp.* TO 'rovlex_user'@'localhost';
FLUSH PRIVILEGES;
```

### 0.4. Настройка Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name test.rovlex.com;

    root /var/www/rovlex/public_html;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/test.rovlex.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/test.rovlex.com/privkey.pem;

    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

**Результат:** Сервер готов к установке WordPress.

---

## Фаза 1: Установка WordPress + темы + плагинов

### 1.1. Установка WordPress

```bash
cd /var/www/rovlex
wget https://wordpress.org/latest.tar.gz
tar xzf latest.tar.gz
mv wordpress public_html
chown -R www-data:www-data public_html
```

Или через WP-CLI:
```bash
wp core download --path=/var/www/rovlex/public_html --locale=en_US
wp core install --url=test.rovlex.com --title="ROVLEX" \
  --admin_user=admin --admin_password=<PASSWORD> \
  --admin_email=admin@rovlex.com
```

### 1.2. Настройка wp-config.php

```php
// Обязательные настройки
define('WP_DEBUG', true);           // На время разработки
define('WP_DEBUG_LOG', true);       // Логи в /wp-content/debug.log
define('WP_DEBUG_DISPLAY', false);  // Не показывать ошибки на фронте
define('DISALLOW_FILE_EDIT', true); // Запрет редактирования файлов из админки
define('WP_MEMORY_LIMIT', '256M');

// Для корректной работы крона
define('DISABLE_WP_CRON', true);    // Используем системный cron
```

Системный cron:
```bash
echo "*/5 * * * * www-data php /var/www/rovlex/public_html/wp-cron.php" | crontab -
```

### 1.3. Установка Listeo (тема)

1. Купить тему Listeo на ThemeForest (~$69)
2. Загрузить через **Appearance → Themes → Upload Theme**
3. Активировать Listeo
4. Установить рекомендуемые плагины при активации:
   - **Listeo Core** (обязательный companion plugin)
   - **Listeo Companion** (дополнительные виджеты)
   - **Elementor** (page builder)
   - **Contact Form 7** (формы)

### 1.4. Настройка Listeo

```
Appearance → Customize:
  - Site Identity: логотип ROVLEX, favicon
  - Colors: Primary #02AF08, Dark #111827
  - Typography: Poppins (headings), Inter (body)

Listeo → Settings:
  - Listing Types: включить "Beauty Salon"
  - Map Provider: Google Maps или OpenStreetMap
  - Submission: включить frontend submission для owners
  - Booking: отключить встроенное бронирование (используем Amelia)
  - Currency: GBP (£)
  - Search: включить поиск по карте
```

### 1.5. Установка Amelia (Pro)

1. Купить Amelia Pro (~$80/год)
2. Загрузить через **Plugins → Add New → Upload Plugin**
3. Активировать
4. Базовая настройка:

```
Amelia → Settings:
  - General:
    - Default Time Slot Step: 15 min
    - Default Appointment Status: pending
    - Use Service Duration for Time Slots: Yes
  - Company:
    - Name: ROVLEX
    - Address: (оставить пустым — у каждого салона свой)
    - Phone: (оставить пустым)
  - Payments:
    - On-site: включить
    - Stripe: настроить позже (Фаза 6)
  - Notifications:
    - Email: включить
    - SMS: отключить пока
  - Roles:
    - Allow managers to: View only assigned locations
```

### 1.6. Создание необходимых страниц

| Страница | URL | Содержимое |
|---|---|---|
| Booking | `/book/` | `[ameliabooking]` |
| Registration | `/register/` | `[rovlex_registration]` (наш шорткод) |
| Login | `/login/` | `[rovlex_login]` (наш шорткод) |
| Dashboard | `/dashboard/` | Listeo dashboard (для owners) |

**Результат:** Чистый WordPress с Listeo + Amelia установлен и настроен.

---

## Фаза 2: Разработка плагина `rovlex-admin-portal`

### Структура файлов

```
plugins/rovlex-admin-portal/
├── rovlex-admin-portal.php          # Главный файл плагина
├── includes/
│   ├── class-roles.php              # Роль rovlex_owner
│   ├── class-admin-cleanup.php      # Скрытие лишнего в wp-admin
│   ├── class-menu.php               # Кастомное меню для owners
│   ├── class-redirects.php          # Редиректы при логине
│   ├── class-login.php              # Кастомная форма логина
│   ├── class-registration.php       # Регистрация + Amelia user
│   └── class-data-isolation.php     # Изоляция данных между owners
├── templates/
│   ├── login.php                    # Шаблон формы логина
│   └── registration.php             # Шаблон формы регистрации
└── assets/
    ├── css/
    │   ├── admin.css                # Стили админки для owners
    │   └── auth.css                 # Стили форм логина/регистрации
    ├── js/
    │   ├── admin.js                 # JS для админки
    │   └── auth.js                  # JS для форм (AJAX)
    └── img/
        └── logo.svg                 # Логотип для брендинга
```

### 2.1. Главный файл (`rovlex-admin-portal.php`)

Точка входа:
- Plugin Name, Version, Dependencies
- Проверка зависимости от Amelia
- Автозагрузка классов из `includes/`
- Хуки активации/деактивации

### 2.2. Роли (`class-roles.php`)

```
При активации плагина:
  → Создать роль "rovlex_owner" с capabilities:
    - read
    - edit_posts (для Listeo frontend submission)
    - upload_files (для фото)
    - wpamelia-manager capabilities (для доступа к Amelia)

При регистрации нового владельца:
  → Назначить роли: rovlex_owner + wpamelia-manager
```

### 2.3. Кастомное меню (`class-menu.php`)

Для пользователей с ролью `rovlex_owner` в wp-admin показывать только:

| Пункт меню | URL в wp-admin | Описание |
|---|---|---|
| Dashboard | `admin.php?page=wpamelia-dashboard` | Сводка Amelia |
| Calendar | `admin.php?page=wpamelia-calendar` | Расписание |
| Appointments | `admin.php?page=wpamelia-appointments` | Записи |
| Employees | `admin.php?page=wpamelia-employees` | Мастера |
| Services | `admin.php?page=wpamelia-services` | Услуги |
| Customers | `admin.php?page=wpamelia-customers` | Клиенты |
| Finance | `admin.php?page=wpamelia-finance` | Финансы |
| My Listing | ссылка на Listeo frontend edit | Мой листинг |
| Settings | кастомная страница | Настройки профиля |

Все остальные пункты wp-admin (Posts, Pages, Comments, Tools, Settings) — скрыть.

### 2.4. Изоляция данных (`class-data-isolation.php`)

**Ключевая фича.** Каждый owner видит только данные своей локации.

Механизм:
```
1. При загрузке wp-admin проверить роль пользователя
2. Если rovlex_owner → получить rovlex_location_id из usermeta
3. Подключить фильтры Amelia:
   - amelia_get_employees_filter → WHERE location_id = X
   - amelia_get_services_filter → WHERE через providers_to_services JOIN
   - amelia_get_appointments_filter → WHERE locationId = X
   - amelia_get_customers_filter → WHERE через appointments JOIN
   - amelia_get_payments_filter → WHERE через appointments JOIN
4. Если location_id ещё не привязан → показать сообщение "Создайте листинг"
```

**Важно:** Amelia Pro предоставляет хуки фильтрации. Если хуки недоступны — использовать JavaScript-фильтрацию как fallback.

### 2.5. Логин (`class-login.php`)

- Шорткод `[rovlex_login]` → кастомная форма
- AJAX-обработчик `wp_ajax_nopriv_rovlex_login`
- При логине: проверить роль → редирект:
  - `rovlex_owner` → `/wp-admin/admin.php?page=wpamelia-dashboard`
  - `administrator` → `/wp-admin/`
  - все остальные → `/`

### 2.6. Регистрация (`class-registration.php`)

- Шорткод `[rovlex_registration]` → кастомная форма
- AJAX-обработчик `wp_ajax_nopriv_rovlex_register`
- При регистрации:

```
1. Валидация (email, пароль, имя)
2. Создать WP-пользователя:
   → wp_insert_user() с ролью rovlex_owner
   → Добавить роль wpamelia-manager
3. Создать Amelia-пользователя:
   → INSERT INTO wp_amelia_users (type='manager', externalId=WP_USER_ID)
   → Пароль: password_hash($password, PASSWORD_BCRYPT, ['cost' => 10])
   → (НЕ wp_hash_password — Amelia использует password_verify!)
4. Автологин → редирект в wp-admin
```

### 2.7. Скрытие стандартного wp-admin (`class-admin-cleanup.php`)

Для роли `rovlex_owner`:
- Скрыть admin bar на фронтенде
- Убрать "Welcome" dashboard widget
- Убрать WordPress news, quick draft и прочие стандартные виджеты
- Добавить брендинг (логотип ROVLEX вместо WordPress)
- Скрыть footer credits

**Результат:** Владельцы видят чистый, брендированный интерфейс.

---

## Фаза 3: Разработка плагина `rovlex-amelia-bridge`

### Структура файлов

```
plugins/rovlex-amelia-bridge/
├── rovlex-amelia-bridge.php         # Главный файл плагина
├── includes/
│   ├── class-location-sync.php      # Листинг → Amelia Location
│   ├── class-data-sync.php          # Крон: мастера/услуги → листинг
│   ├── class-listing-display.php    # Отображение на листинге
│   └── class-booking-page.php       # Страница бронирования
├── assets/
│   ├── css/
│   │   └── listing-display.css      # Стили карточек мастеров/услуг
│   └── js/
│       └── booking.js               # JS для кнопки бронирования
└── templates/
    ├── staff-card.php               # Шаблон карточки мастера
    ├── service-row.php              # Шаблон строки услуги
    └── booking-button.php           # Шаблон кнопки "Забронировать"
```

### 3.1. Автосоздание локации (`class-location-sync.php`)

Хуки:
- `save_post_listing` (при сохранении листинга в Listeo)
- `listeo_after_submit_listing` (при frontend submission)

Логика:
```
1. Листинг опубликован (status = publish)
2. Проверить: есть ли уже _amelia_location_id в post_meta?
   → Если да — обновить существующую локацию
   → Если нет — создать новую

3. Создание Amelia Location:
   INSERT INTO wp_amelia_locations SET
     status = 'visible',
     name = post_title,
     address = _listing_address (meta),
     phone = _listing_phone (meta),
     latitude = _listing_lat (meta),
     longitude = _listing_lng (meta),
     description = post_excerpt

4. Сохранить маппинг:
   → update_post_meta($listing_id, '_amelia_location_id', $location_id)
   → update_user_meta($author_id, 'rovlex_location_id', $location_id)
   → INSERT INTO wp_rovlex_amelia_map (listing_id, amelia_location_id, wp_user_id)
```

Таблица маппинга (создаётся при активации плагина):
```sql
CREATE TABLE wp_rovlex_amelia_map (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  listing_id BIGINT UNSIGNED NOT NULL,
  amelia_location_id BIGINT UNSIGNED NOT NULL,
  wp_user_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY listing_location (listing_id, amelia_location_id)
);
```

### 3.2. Крон-синхронизация (`class-data-sync.php`)

Каждые 15 минут:
```
1. Получить все маппинги из wp_rovlex_amelia_map
2. Для каждого маппинга:
   a. Получить мастеров из wp_amelia_users
      WHERE type='employee'
      AND id IN (SELECT providerId FROM wp_amelia_providers_to_locations
                 WHERE locationId = X)

   b. Получить услуги:
      SELECT DISTINCT s.* FROM wp_amelia_services s
      JOIN wp_amelia_providers_to_services ps ON s.id = ps.serviceId
      JOIN wp_amelia_providers_to_locations pl ON ps.userId = pl.userId
      WHERE pl.locationId = X

   c. Сгенерировать HTML:
      → _rovlex_staff_html: карточки мастеров (фото, имя, должность)
      → _rovlex_services_html: таблица услуг (название, длительность, цена)
      → _rovlex_staff_count: число мастеров
      → _rovlex_services_count: число услуг

   d. Обновить post_meta листинга
```

### 3.3. Отображение на листинге (`class-listing-display.php`)

Интеграция с Listeo через хуки:

```
1. Таб "Staff & Services" на странице листинга:
   → listeo_listing_data_tabs — добавить таб
   → listeo_listing_data_panels — вывести содержимое

2. Карточки мастеров:
   → Фото (круглое), Имя, Должность/Специализация

3. Список услуг:
   → Название | Длительность | Цена

4. Кнопка "Забронировать":
   → listeo_single_listing_after_content
   → listeo_sidebar_listing_actions
   → Ссылка: /book/?location={amelia_location_id}
```

### 3.4. Страница бронирования (`class-booking-page.php`)

- На странице `/book/` отображается шорткод `[ameliabooking]`
- Если передан `?location=ID` → автоматически фильтровать по локации
- Шорткод: `[ameliabooking location="{ID}"]`

**Результат:** Два плагина написаны, мост между Listeo и Amelia работает.

---

## Фаза 4: Интеграция и связка плагинов

### 4.1. Путь владельца (Owner Journey)

```
1. /register/ → Регистрация
   └─ WP user (rovlex_owner) + Amelia user (manager) созданы

2. /wp-admin/ → Логин владельца
   └─ Кастомное меню, брендинг ROVLEX

3. Listeo Frontend → Создание листинга
   └─ save_post_listing HOOK:
      ├─ Amelia Location создана автоматически
      ├─ location_id привязан к owner в usermeta
      └─ Маппинг записан в wp_rovlex_amelia_map

4. /wp-admin/ → Employees → Add New
   └─ Owner добавляет мастеров (видит только свою локацию)

5. /wp-admin/ → Services → Add New
   └─ Owner добавляет услуги

6. [CRON каждые 15 мин]
   └─ Мастера и услуги синхронизированы на листинг
```

### 4.2. Путь клиента (Customer Journey)

```
1. Главная страница / Карта → Поиск салонов

2. Клик на салон → Страница листинга:
   ├─ Описание, фото, адрес (Listeo)
   ├─ Таб "Staff & Services":
   │   ├─ Карточки мастеров
   │   └─ Список услуг с ценами
   └─ Кнопка "Забронировать"

3. /book/?location=ID → Форма бронирования Amelia:
   └─ Выбор: мастер → услуга → дата/время → подтверждение
```

### 4.3. Проверка зависимостей

Порядок загрузки:
1. Amelia (должна быть активна)
2. Listeo Core (должен быть активен)
3. rovlex-admin-portal (зависит от Amelia)
4. rovlex-amelia-bridge (зависит от Amelia + Listeo Core)

Каждый плагин проверяет зависимости при активации:
```php
// В rovlex-admin-portal.php
if (!class_exists('AmeliaBooking\Plugin')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('ROVLEX Admin Portal requires Amelia plugin to be active.');
}

// В rovlex-amelia-bridge.php
if (!class_exists('AmeliaBooking\Plugin') || !function_exists('listeo_core_init')) {
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die('ROVLEX Amelia Bridge requires both Amelia and Listeo Core plugins.');
}
```

**Результат:** Оба плагина работают как единая система.

---

## Фаза 5: Настройка контента и категорий

### 5.1. Категории листингов в Listeo

```
Beauty Salon (основная категория)
├── Hair Salon
├── Nail Salon
├── Makeup Studio
├── Skincare & Facial
├── Massage & Body
├── Barber Shop
└── Multi-Service Salon
```

### 5.2. Категории услуг в Amelia

```
Hair Services
├── Haircut (Women) — 45 min — £35+
├── Haircut (Men) — 30 min — £20+
├── Hair Coloring — 120 min — £80+
├── Highlights — 90 min — £70+
├── Blowout — 30 min — £25+
└── Hair Treatment — 45 min — £40+

Nail Services
├── Manicure — 30 min — £20+
├── Pedicure — 45 min — £25+
├── Gel Nails — 60 min — £30+
└── Nail Art — 30 min — £15+

(и т.д. по категориям)
```

**Важно:** Категории услуг в Amelia создаёт каждый owner самостоятельно (но можно создать шаблонные для удобства).

### 5.3. Тестовые данные

Для тестирования создать:
- 2-3 тестовых owner-а
- 2-3 листинга с разными адресами
- По 2-3 мастера и 3-5 услуг на каждый салон
- Несколько тестовых бронирований

**Результат:** Контент настроен, система готова к тестированию.

---

## Фаза 6: Тестирование (End-to-End)

### 6.1. Тест: Регистрация владельца

```
1. Открыть /register/
2. Заполнить: имя, email, пароль, телефон
3. Нажать "Register"
4. ✓ WP user создан с ролями rovlex_owner + wpamelia-manager
5. ✓ Amelia user создан (type: manager, externalId = WP user ID)
6. ✓ Пароль в Amelia: bcrypt $2y$10$... (НЕ $wp$2y$10$...)
7. ✓ Автоматический логин → редирект в /wp-admin/
8. ✓ Кастомное меню (не стандартный wp-admin)
```

### 6.2. Тест: Создание листинга

```
1. Owner в Listeo dashboard → "Add Listing"
2. Заполнить: название, описание, адрес, фото, телефон
3. Опубликовать
4. ✓ Amelia Location создана (название, адрес, координаты)
5. ✓ _amelia_location_id записан в post_meta листинга
6. ✓ rovlex_location_id записан в usermeta owner-а
7. ✓ Маппинг в wp_rovlex_amelia_map
```

### 6.3. Тест: Добавление мастеров и услуг

```
1. Owner в wp-admin → Employees → Add New
2. Создать мастера (имя, фото, описание)
3. Привязать к локации
4. Owner → Services → Add New
5. Создать услугу (название, цена £35, длительность 45 мин)
6. Привязать к мастеру
7. Запустить крон вручную (или подождать 15 мин)
8. ✓ На листинге появились карточки мастеров
9. ✓ На листинге появился список услуг с ценами
```

### 6.4. Тест: Бронирование клиентом

```
1. Клиент открывает страницу листинга
2. Видит мастеров и услуги
3. Нажимает "Забронировать"
4. → /book/?location=ID
5. Amelia форма: выбор мастера → услуги → даты → времени
6. Подтверждение
7. ✓ Запись создана в Amelia (привязана к location)
8. ✓ Owner видит запись в своём календаре
```

### 6.5. Тест: Изоляция данных

```
1. Создать Owner A и Owner B
2. Каждый создаёт листинг → свою локацию
3. Каждый добавляет мастеров и услуги
4. Owner A в wp-admin:
   ✓ Видит только свою локацию
   ✓ Видит только своих мастеров
   ✓ Видит только свои услуги
   ✓ Видит только свои записи
   ✗ НЕ видит данные Owner B
5. Owner B — аналогично
```

**Результат:** Все сценарии протестированы и работают.

---

## Фаза 7: Продакшн-подготовка

### 7.1. Безопасность

- [ ] `WP_DEBUG = false` в wp-config.php
- [ ] Удалить debug.log
- [ ] Проверить file permissions (644 файлы, 755 директории)
- [ ] Убедиться что X-Frame-Options: SAMEORIGIN для wp-admin
- [ ] Настроить CSP headers
- [ ] Убедиться что SQL-запросы используют $wpdb->prepare()
- [ ] Проверить nonce-верификацию во всех AJAX-обработчиках
- [ ] Настроить fail2ban для защиты от brute force
- [ ] Ограничить попытки логина (Limit Login Attempts plugin)

### 7.2. Платежи (Stripe Connect)

```
Amelia → Settings → Payments → Stripe:
  - API Key: sk_live_...
  - Public Key: pk_live_...
  - Webhook: https://rovlex.com/wp-json/amelia/v1/stripe-webhook

Stripe Connect:
  - Каждый owner подключает свой Stripe аккаунт
  - Комиссия ROVLEX: 10-25% автоматически
  - Выплаты мастерам: через Stripe Connect payouts
```

### 7.3. Производительность

- [ ] Установить кэширование (Redis или WP Super Cache)
- [ ] Оптимизировать изображения (ShortPixel или Imagify)
- [ ] Включить GZIP compression
- [ ] Настроить CDN (CloudFlare)
- [ ] Оптимизировать SQL-запросы в изоляции (кэширование location_ids)
- [ ] Transient-кэширование для staff/services HTML на листинге

### 7.4. Мониторинг

- [ ] Логирование ошибок обоих плагинов
- [ ] Мониторинг крона (timestamp последней синхронизации)
- [ ] UptimeRobot для мониторинга доступности
- [ ] Ежедневные бэкапы БД + файлов

### 7.5. SEO и аналитика

- [ ] Yoast SEO или RankMath
- [ ] Google Analytics 4
- [ ] Google Search Console
- [ ] Schema.org разметка для салонов (LocalBusiness)

**Результат:** Система готова к продакшну.

---

## Порядок выполнения и зависимости

```
Фаза 0: Сервер           ──┐
                            ├─→ Фаза 1: WordPress + Listeo + Amelia
                            │
                            ├─→ Фаза 2: rovlex-admin-portal ──┐
                            │                                  ├─→ Фаза 4: Интеграция ──→ Фаза 5 ──→ Фаза 6 ──→ Фаза 7
                            └─→ Фаза 3: rovlex-amelia-bridge ─┘
```

**Фазы 2 и 3 можно разрабатывать параллельно**, так как плагины независимы друг от друга до момента интеграции (Фаза 4).

---

## Оценка объёма работ

| Фаза | Описание | Файлов кода |
|---|---|---|
| 0 | Сервер | 0 (конфигурация) |
| 1 | WordPress + Listeo + Amelia | 0 (установка) |
| 2 | rovlex-admin-portal | ~15 файлов (~900 строк PHP + CSS + JS) |
| 3 | rovlex-amelia-bridge | ~10 файлов (~600 строк PHP + CSS + JS) |
| 4 | Интеграция | ~2 файла (доработки в существующих) |
| 5 | Контент | 0 (настройка через UI) |
| 6 | Тестирование | 0 (ручное тестирование) |
| 7 | Продакшн | 0 (конфигурация) |

**Итого кастомного кода:** ~25 файлов, ~1500 строк.

---

## Известные технические нюансы

### 1. Хеширование паролей
WordPress и Amelia используют разные алгоритмы:
- WordPress: `wp_hash_password()` → `$wp$2y$10$...`
- Amelia: `password_hash()` → `$2y$10$...`

При регистрации нужно создавать два хеша — один для WP, другой для Amelia.

### 2. Amelia Pro хуки
Фильтры типа `amelia_get_employees_filter` доступны только в Pro-версии. Нужна именно Pro.

### 3. Listeo frontend submission
Хук `save_post_listing` срабатывает и при draft, и при publish. Нужно проверять `post_status === 'publish'`.

### 4. WP Cron
По умолчанию WP Cron работает только при визите на сайт. Для надёжной 15-минутной синхронизации нужен системный cron.

### 5. Multi-salon
Текущая архитектура: один owner = один салон (одна локация). Для поддержки нескольких салонов у одного owner-а нужно хранить массив location_ids. Это можно добавить позже.

---

## Вопросы перед стартом

1. **Сервер готов?** Есть ли уже VDS/VPS с SSH-доступом, или нужно арендовать?
2. **Лицензии куплены?** Listeo ($69), Amelia Pro ($80/год) — есть?
3. **Домен:** test.rovlex.com или другой для разработки?
4. **Платежи:** Stripe Connect нужен сразу или после тестирования?
5. **Мульти-салон:** Один owner = один салон? Или может быть несколько?
6. **Язык интерфейса:** EN или RU (или мультиязычный)?

---

**Автор:** Claude
**Дата:** 17 февраля 2026
**Статус:** План для чистой установки с нуля
