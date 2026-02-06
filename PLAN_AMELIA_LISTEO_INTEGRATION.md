# 🔧 ПЛАН РАЗРАБОТКИ: Интеграция Amelia + Listeo
## rovlex.com — Beauty & Handyman Marketplace

**Версия:** 1.0  
**Дата:** 6 февраля 2026  
**Сайт:** rovlex.com (test.rovlex.com для тестирования)  
**Статус:** Готов к реализации

---

## 📌 Общая схема

```
OWNER FLOW:
═══════════════════════════════════════════════════════════════

1. Фронтенд Listeo           2. WP-Admin Amelia           3. Публичная страница
   /add-listing/                 /wp-admin/                    /listing/salon-name/
                                                              
┌─────────────────────┐    ┌──────────────────────┐    ┌─────────────────────────┐
│ Овнер заполняет     │    │ Овнер добавляет:     │    │ Клиент видит:           │
│ листинг:            │    │                      │    │                         │
│ • Название салона    │───►│ • Мастеров (staff)   │───►│ • Инфо о салоне (Listeo)│
│ • Адрес, описание   │    │ • Услуги (services)  │    │ • Секция "Мастера"      │
│ • Фото, контакты    │    │ • Цены, длительность │    │ • Секция "Услуги+Цены"  │
│                     │    │ • Расписание         │    │ • Кнопка "Book Now" ──────► Amelia
└─────────────────────┘    └──────────────────────┘    └─────────────────────────┘
         │                          │                           ▲
         │  AUTO: создаётся         │  SYNC: данные из          │
         │  Amelia Location         │  Amelia → Listeo поля     │
         ▼                          ▼                           │
   amelia_location_id         cron каждые 15 мин          клиент видит
   сохраняется в              обновляет post_meta         актуальные данные
   post_meta листинга         листинга                    из Amelia
```

---

## 📋 Содержание

1. [Фаза 0: Подготовка окружения](#фаза-0)
2. [Фаза 1: Автоматическое создание Amelia Location](#фаза-1)
3. [Фаза 2: Редирект овнера в Amelia после создания листинга](#фаза-2)
4. [Фаза 3: Создание кастомных полей в Listeo](#фаза-3)
5. [Фаза 4: Синхронизация Amelia → Listeo](#фаза-4)
6. [Фаза 5: Кнопка "Book Now" с редиректом на Amelia](#фаза-5)
7. [Фаза 6: Тестирование](#фаза-6)

---

## Фаза 0: Подготовка окружения {#фаза-0}

### Задача 0.1: Проверить Amelia

**Что сделать:**
```bash
# SSH на сервер
ssh root@213.155.28.121

# Найти путь к WordPress rovlex.com
# (определить document root для rovlex.com в ISPmanager)

# Проверить что Amelia активна
wp plugin list --path=/path/to/rovlex.com | grep amelia

# Проверить версию Amelia
wp option get amelia_version --path=/path/to/rovlex.com

# Проверить что REST API Amelia доступен
curl -s https://rovlex.com/wp-json/amelia/v1/entities | head -50
```

**Критерий успеха:**
- [ ] Amelia плагин активен
- [ ] Версия Amelia задокументирована
- [ ] REST API отвечает

### Задача 0.2: Проверить Listeo и child theme

```bash
# Проверить активную тему
wp theme list --status=active --path=/path/to/rovlex.com

# Проверить child theme
ls -la /path/to/rovlex.com/wp-content/themes/listeo-child/

# Проверить functions.php child темы
cat /path/to/rovlex.com/wp-content/themes/listeo-child/functions.php
```

**Критерий успеха:**
- [ ] Listeo child theme активен
- [ ] functions.php child темы доступен для редактирования
- [ ] Путь к child theme задокументирован

### Задача 0.3: Определить структуру БД Amelia

```bash
# Подключиться к MySQL
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_

# Посмотреть таблицы Amelia
SHOW TABLES LIKE '%amelia%';

# Структура ключевых таблиц
DESCRIBE wp_amelia_locations;
DESCRIBE wp_amelia_users;        -- staff/employees
DESCRIBE wp_amelia_services;
DESCRIBE wp_amelia_providers_to_services;
```

**Сохранить структуру в файл:**
```bash
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "DESCRIBE wp_amelia_locations;" > /tmp/amelia_schema.txt
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "DESCRIBE wp_amelia_users;" >> /tmp/amelia_schema.txt
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "DESCRIBE wp_amelia_services;" >> /tmp/amelia_schema.txt
```

**Критерий успеха:**
- [ ] Таблицы Amelia существуют
- [ ] Схема таблиц задокументирована
- [ ] Поля `wp_amelia_locations` известны (name, address, latitude, longitude и т.д.)

### Задача 0.4: Определить post_type листингов Listeo

```bash
# Узнать CPT листингов
wp post-type list --path=/path/to/rovlex.com | grep listing

# Посмотреть meta-ключи существующего листинга (если есть)
wp post meta list <LISTING_ID> --path=/path/to/rovlex.com

# Проверить hooks Listeo при создании листинга
grep -r "listeo_after_submit" /path/to/rovlex.com/wp-content/themes/listeo/
grep -r "listeo_listing_saved" /path/to/rovlex.com/wp-content/themes/listeo/
grep -r "save_post_listing" /path/to/rovlex.com/wp-content/themes/listeo/
```

**Критерий успеха:**
- [ ] CPT листинга определён (предположительно `listing`)
- [ ] Известны hooks при создании/сохранении листинга
- [ ] Meta-ключи Listeo задокументированы

---

## Фаза 1: Автоматическое создание Amelia Location {#фаза-1}

### Задача 1.1: Создать плагин rovlex-amelia-bridge

**Почему отдельный плагин, а не functions.php:**
- Чистое разделение — легко отключить/обновить
- Не зависит от смены темы
- Легко передать другому разработчику

**Создать файл:** `wp-content/plugins/rovlex-amelia-bridge/rovlex-amelia-bridge.php`

```php
<?php
/**
 * Plugin Name: ROVLEX Amelia Bridge
 * Description: Интеграция Amelia Booking с Listeo листингами
 * Version: 1.0.0
 * Author: ROVLEX Team
 */

if (!defined('ABSPATH')) exit;

// Константы
define('ROVLEX_AB_VERSION', '1.0.0');
define('ROVLEX_AB_PATH', plugin_dir_path(__FILE__));

// Подключить модули
require_once ROVLEX_AB_PATH . 'includes/class-location-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-data-sync.php';
require_once ROVLEX_AB_PATH . 'includes/class-listing-display.php';
require_once ROVLEX_AB_PATH . 'includes/class-admin-redirect.php';

// Инициализация
add_action('plugins_loaded', function() {
    // Проверить что Amelia активна
    if (!class_exists('AmeliaBooking\Plugin')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>ROVLEX Amelia Bridge:</strong> Плагин Amelia Booking должен быть активен.';
            echo '</p></div>';
        });
        return;
    }
    
    // Инициализировать модули
    new Rovlex_Location_Sync();
    new Rovlex_Data_Sync();
    new Rovlex_Listing_Display();
    new Rovlex_Admin_Redirect();
});

// Создать таблицу связей при активации
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    
    // Таблица маппинга listing_id ↔ amelia_location_id
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rovlex_amelia_map (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id bigint(20) UNSIGNED NOT NULL,
        amelia_location_id bigint(20) UNSIGNED NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY listing_id (listing_id),
        UNIQUE KEY amelia_location_id (amelia_location_id)
    ) $charset;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
});
```

**Структура плагина:**
```
wp-content/plugins/rovlex-amelia-bridge/
├── rovlex-amelia-bridge.php          (главный файл)
├── includes/
│   ├── class-location-sync.php       (Фаза 1: создание Location)
│   ├── class-data-sync.php           (Фаза 4: синхронизация данных)
│   ├── class-listing-display.php     (Фаза 3+5: отображение + Book Now)
│   └── class-admin-redirect.php      (Фаза 2: редирект в Amelia)
└── README.md
```

### Задача 1.2: Реализовать auto-create Location при создании листинга

**Файл:** `includes/class-location-sync.php`

```php
<?php
/**
 * При создании/обновлении листинга Listeo → 
 * автоматически создать/обновить Location в Amelia
 */
class Rovlex_Location_Sync {

    public function __construct() {
        // Hook после сохранения листинга через фронтенд Listeo
        // ВАЖНО: Нужно определить правильный hook — см. Задачу 0.4
        
        // Вариант A: Listeo-специфичный hook
        add_action('listeo_after_submit_listing', [$this, 'on_listing_created'], 10, 1);
        
        // Вариант B: стандартный WP hook (как fallback)
        add_action('save_post_listing', [$this, 'on_listing_saved'], 20, 3);
    }

    /**
     * При создании листинга через фронтенд Listeo
     */
    public function on_listing_created($listing_id) {
        $this->sync_location($listing_id);
    }
    
    /**
     * При сохранении листинга (wp-admin или фронтенд)
     */
    public function on_listing_saved($post_id, $post, $update) {
        // Пропустить автосохранение
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        
        // Пропустить ревизии
        if (wp_is_post_revision($post_id)) return;
        
        // Только для опубликованных или pending
        if (!in_array($post->post_status, ['publish', 'pending'])) return;
        
        $this->sync_location($post_id);
    }
    
    /**
     * Создать или обновить Amelia Location
     */
    private function sync_location($listing_id) {
        global $wpdb;
        
        $post = get_post($listing_id);
        if (!$post) return;
        
        // Собрать данные из листинга Listeo
        $name = $post->post_title;
        $address = get_post_meta($listing_id, '_address', true); // ключ Listeo для адреса
        $phone = get_post_meta($listing_id, '_phone', true);
        $email = get_post_meta($listing_id, '_email', true);
        $lat = get_post_meta($listing_id, '_geolocation_lat', true);
        $lng = get_post_meta($listing_id, '_geolocation_long', true);
        
        // Проверить существующий маппинг
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT amelia_location_id FROM {$wpdb->prefix}rovlex_amelia_map 
             WHERE listing_id = %d",
            $listing_id
        ));
        
        // Данные для Amelia Location
        $location_data = [
            'name'        => $name,
            'address'     => $address ?: '',
            'phone'       => $phone ?: '',
            'latitude'    => $lat ? floatval($lat) : 0,
            'longitude'   => $lng ? floatval($lng) : 0,
            'description' => wp_trim_words($post->post_content, 50),
            'status'      => 'visible'
        ];
        
        if ($existing) {
            // UPDATE существующей Location
            $this->update_amelia_location($existing, $location_data);
        } else {
            // INSERT новой Location
            $amelia_id = $this->create_amelia_location($location_data);
            
            if ($amelia_id) {
                // Сохранить маппинг
                $wpdb->insert(
                    $wpdb->prefix . 'rovlex_amelia_map',
                    [
                        'listing_id'        => $listing_id,
                        'amelia_location_id' => $amelia_id
                    ],
                    ['%d', '%d']
                );
                
                // Также сохранить в post_meta для быстрого доступа
                update_post_meta($listing_id, '_amelia_location_id', $amelia_id);
            }
        }
    }
    
    /**
     * Создать Location в таблице Amelia напрямую через SQL
     * 
     * Альтернатива: использовать Amelia REST API
     * POST /wp-json/amelia/v1/locations
     * Но прямой SQL надёжнее для серверного кода
     */
    private function create_amelia_location($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'amelia_locations',
            [
                'status'      => $data['status'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? '',
                'address'     => $data['address'],
                'phone'       => $data['phone'],
                'latitude'    => $data['latitude'],
                'longitude'   => $data['longitude'],
            ],
            ['%s', '%s', '%s', '%s', '%s', '%f', '%f']
        );
        
        if ($result === false) {
            error_log('ROVLEX Amelia Bridge: Не удалось создать Location. Error: ' . $wpdb->last_error);
            return false;
        }
        
        $location_id = $wpdb->insert_id;
        error_log("ROVLEX Amelia Bridge: Создана Location ID={$location_id} для листинга");
        
        return $location_id;
    }
    
    /**
     * Обновить существующую Location
     */
    private function update_amelia_location($amelia_id, $data) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'amelia_locations',
            [
                'name'        => $data['name'],
                'address'     => $data['address'],
                'phone'       => $data['phone'],
                'latitude'    => $data['latitude'],
                'longitude'   => $data['longitude'],
                'description' => $data['description'] ?? '',
            ],
            ['id' => $amelia_id],
            ['%s', '%s', '%s', '%f', '%f', '%s'],
            ['%d']
        );
    }
    
    /**
     * Получить Amelia Location ID для листинга
     */
    public static function get_amelia_location_id($listing_id) {
        return get_post_meta($listing_id, '_amelia_location_id', true);
    }
}
```

**⚠️ ВАЖНО для разработчика:**
Нужно верифицировать:
1. Правильный hook Listeo при создании листинга через фронтенд (поискать в коде темы)
2. Правильные meta-ключи Listeo для адреса, телефона, координат
3. Точные имена колонок таблицы `wp_amelia_locations`

Для верификации выполнить:
```bash
# Найти hooks Listeo
grep -rn "do_action.*listing" /path/to/rovlex.com/wp-content/themes/listeo/includes/ | head -20
grep -rn "do_action.*submit" /path/to/rovlex.com/wp-content/themes/listeo/includes/ | head -20

# Найти meta-ключи адреса
grep -rn "_address\|_geolocation\|_phone\|_email" /path/to/rovlex.com/wp-content/themes/listeo/includes/ | head -20

# Точные колонки Amelia
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ -e "DESCRIBE wp_amelia_locations;"
```

**Критерий успеха Фазы 1:**
- [ ] При создании листинга через /add-listing/ автоматически создаётся Amelia Location
- [ ] В post_meta листинга сохраняется `_amelia_location_id`
- [ ] В таблице `wp_rovlex_amelia_map` появляется запись
- [ ] При обновлении листинга — Location обновляется (а не создаётся новая)

---

## Фаза 2: Редирект овнера в Amelia после создания листинга {#фаза-2}

### Задача 2.1: Перенаправить овнера в wp-admin Amelia

**Файл:** `includes/class-admin-redirect.php`

```php
<?php
/**
 * После создания листинга → редирект в wp-admin Amelia
 * для добавления мастеров и услуг
 */
class Rovlex_Admin_Redirect {

    public function __construct() {
        // Перехватить редирект Listeo после создания листинга
        add_filter('listeo_submit_redirect', [$this, 'redirect_to_amelia'], 10, 2);
        
        // Добавить уведомление в wp-admin
        add_action('admin_notices', [$this, 'show_amelia_notice']);
        
        // Добавить кнопку "Manage Staff & Services" на странице листинга (фронтенд)
        add_action('listeo_dashboard_listing_actions', [$this, 'add_manage_button'], 10, 1);
    }
    
    /**
     * Перенаправить на страницу Amelia Employees после создания листинга
     * 
     * ⚠️ ВАЖНО: Точное имя фильтра зависит от версии Listeo
     * Нужно найти фильтр в файле:
     * listeo/includes/class-listeo-submit.php
     * или
     * listeo/includes/paid-listings/class-wc-paid-listing-submit.php
     */
    public function redirect_to_amelia($redirect_url, $listing_id) {
        $amelia_location_id = get_post_meta($listing_id, '_amelia_location_id', true);
        
        if ($amelia_location_id) {
            // Редирект в Amelia → Employees с фильтром по локации
            return admin_url(
                'admin.php?page=wpamelia-employees&location=' . $amelia_location_id 
                . '&rovlex_listing=' . $listing_id
            );
        }
        
        return $redirect_url;
    }
    
    /**
     * Показать уведомление "Добавьте мастеров и услуги"
     */
    public function show_amelia_notice() {
        if (!isset($_GET['rovlex_listing'])) return;
        
        $listing_id = intval($_GET['rovlex_listing']);
        $listing_title = get_the_title($listing_id);
        
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>🎉 Листинг "' . esc_html($listing_title) . '" создан!</strong></p>';
        echo '<p>Теперь добавьте мастеров (Employees) и услуги (Services) для этого салона.</p>';
        echo '<p>';
        echo '<a href="' . admin_url('admin.php?page=wpamelia-employees') . '" class="button button-primary">→ Добавить мастеров</a> ';
        echo '<a href="' . admin_url('admin.php?page=wpamelia-services') . '" class="button">→ Добавить услуги</a>';
        echo '</p>';
        echo '</div>';
    }
    
    /**
     * Добавить кнопку на дашборде овнера (My Listings)
     */
    public function add_manage_button($listing_id) {
        $amelia_location_id = get_post_meta($listing_id, '_amelia_location_id', true);
        
        if ($amelia_location_id) {
            echo '<a href="' . admin_url('admin.php?page=wpamelia-employees') . '" ';
            echo 'class="button" target="_blank" title="Manage Staff & Services in Amelia">';
            echo '👥 Staff & Services</a> ';
        }
    }
}
```

**⚠️ ВАЖНО для разработчика:**
Нужно найти точный фильтр/хук редиректа в Listeo:
```bash
# Поиск редиректа после submit
grep -rn "redirect\|location.*header\|wp_redirect\|wp_safe_redirect" \
  /path/to/rovlex.com/wp-content/themes/listeo/includes/ | grep -i submit
```

Если фильтра нет — альтернативный подход через JavaScript:
```php
// Добавить JS на страницу "Листинг успешно создан"
add_action('wp_footer', function() {
    if (is_page('submission-complete') || /* условие */ ) {
        $listing_id = /* получить ID */;
        echo '<script>
            setTimeout(function() {
                window.location.href = "' . admin_url('admin.php?page=wpamelia-employees') . '";
            }, 3000); // через 3 секунды
        </script>';
    }
});
```

**Критерий успеха Фазы 2:**
- [ ] После создания листинга овнер попадает в wp-admin Amelia
- [ ] Видит уведомление с названием созданного листинга
- [ ] Есть кнопки "Добавить мастеров" и "Добавить услуги"
- [ ] На дашборде My Listings есть кнопка "Staff & Services"

---

## Фаза 3: Создание кастомных полей в Listeo {#фаза-3}

### Задача 3.1: Зарегистрировать секцию "Staff & Services" через Listeo

**Подход:** Listeo позволяет добавлять кастомные секции в шаблон листинга через `listeo_core_listing_fields` фильтр или через настройки темы (Theme Options → Listings → Custom Fields).

**Вариант A: Через Theme Options (рекомендуется — штатный функционал)**

В WordPress Admin → Listeo → Custom Fields → Listing Page:

| Поле | Тип | Ключ | Описание |
|------|-----|------|----------|
| Staff Members | textarea (read-only) | `_rovlex_staff_html` | HTML с карточками мастеров |
| Services & Prices | textarea (read-only) | `_rovlex_services_html` | HTML с таблицей услуг |
| Amelia Location ID | hidden | `_amelia_location_id` | ID локации в Amelia |

**Вариант B: Через код (если Theme Options недостаточно)**

**Файл:** добавить в child theme `functions.php` или в плагин:

```php
<?php
/**
 * Зарегистрировать кастомные поля Listeo для отображения данных Amelia
 * 
 * ⚠️ ВАЖНО: Точный фильтр зависит от версии Listeo
 * Проверить доступные фильтры:
 * grep -rn "apply_filters.*listing.*fields\|listing_data_tabs" themes/listeo/
 */

// Добавить кастомный таб на странице листинга
add_filter('listeo_listing_data_tabs', function($tabs) {
    $tabs['staff-services'] = [
        'label'    => __('Staff & Services', 'rovlex'),
        'target'   => 'staff-services-tab',
        'priority' => 25,
        'icon'     => 'sl sl-icon-people',
    ];
    return $tabs;
});

// Контент таба
add_action('listeo_listing_data_panels', function() {
    global $post;
    $listing_id = $post->ID;
    
    $staff_html = get_post_meta($listing_id, '_rovlex_staff_html', true);
    $services_html = get_post_meta($listing_id, '_rovlex_services_html', true);
    
    echo '<div id="staff-services-tab" class="listing-section">';
    
    if ($staff_html) {
        echo '<h3 class="listing-desc-headline">Our Team</h3>';
        echo '<div class="rovlex-staff-grid">' . $staff_html . '</div>';
    }
    
    if ($services_html) {
        echo '<h3 class="listing-desc-headline">Services & Prices</h3>';
        echo '<div class="rovlex-services-list">' . $services_html . '</div>';
    }
    
    if (!$staff_html && !$services_html) {
        echo '<p>Information about staff and services will be available soon.</p>';
    }
    
    echo '</div>';
});
```

### Задача 3.2: CSS стили для секции

**Файл:** `wp-content/themes/listeo-child/style.css` (добавить в конец)

```css
/* ===== ROVLEX: Staff & Services Section ===== */

.rovlex-staff-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.rovlex-staff-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: box-shadow 0.2s ease;
}

.rovlex-staff-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.rovlex-staff-card .staff-photo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 12px;
    display: block;
    background: #f3f4f6;
}

.rovlex-staff-card .staff-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
    color: #111827;
}

.rovlex-staff-card .staff-position {
    color: #6b7280;
    font-size: 13px;
}

/* Services Table */
.rovlex-services-list {
    margin-bottom: 30px;
}

.rovlex-service-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid #f3f4f6;
}

.rovlex-service-item:last-child {
    border-bottom: none;
}

.rovlex-service-info {
    flex: 1;
}

.rovlex-service-name {
    font-weight: 600;
    font-size: 15px;
    color: #111827;
    margin-bottom: 2px;
}

.rovlex-service-duration {
    font-size: 13px;
    color: #6b7280;
}

.rovlex-service-price {
    font-weight: 700;
    font-size: 16px;
    color: #02AF08;
    white-space: nowrap;
    margin-left: 20px;
}

/* Book Now Button */
.rovlex-book-btn {
    display: inline-block;
    background: #02AF08;
    color: #fff;
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    text-decoration: none;
    margin-top: 20px;
    transition: background 0.2s ease;
}

.rovlex-book-btn:hover {
    background: #029207;
    color: #fff;
}
```

**Критерий успеха Фазы 3:**
- [ ] На странице листинга есть секция "Staff & Services"
- [ ] Секция показывает карточки мастеров (фото, имя, специализация)
- [ ] Секция показывает список услуг (название, длительность, цена)
- [ ] Стили соответствуют дизайну Rovlex (#02AF08)

---

## Фаза 4: Синхронизация Amelia → Listeo {#фаза-4}

### Задача 4.1: Cron-задача для синхронизации данных

**Файл:** `includes/class-data-sync.php`

```php
<?php
/**
 * Синхронизация данных из Amelia → post_meta листингов Listeo
 * 
 * Запускается по cron каждые 15 минут.
 * Читает staff и services из таблиц Amelia,
 * генерирует HTML и записывает в post_meta листинга.
 */
class Rovlex_Data_Sync {

    public function __construct() {
        // Регистрация cron
        add_action('init', [$this, 'schedule_sync']);
        add_action('rovlex_amelia_sync', [$this, 'run_sync']);
        
        // Также синхронизировать при ручном запросе
        add_action('wp_ajax_rovlex_force_sync', [$this, 'ajax_force_sync']);
        
        // Дерегистрация cron при деактивации плагина
        register_deactivation_hook(
            dirname(__DIR__) . '/rovlex-amelia-bridge.php',
            [$this, 'unschedule_sync']
        );
    }
    
    /**
     * Зарегистрировать cron-задачу
     */
    public function schedule_sync() {
        if (!wp_next_scheduled('rovlex_amelia_sync')) {
            wp_schedule_event(time(), 'fifteen_minutes', 'rovlex_amelia_sync');
        }
    }
    
    /**
     * Убрать cron при деактивации
     */
    public function unschedule_sync() {
        wp_clear_scheduled_hook('rovlex_amelia_sync');
    }
    
    /**
     * Основная функция синхронизации
     */
    public function run_sync() {
        global $wpdb;
        
        // Получить все маппинги listing ↔ amelia_location
        $mappings = $wpdb->get_results(
            "SELECT listing_id, amelia_location_id 
             FROM {$wpdb->prefix}rovlex_amelia_map"
        );
        
        if (empty($mappings)) return;
        
        foreach ($mappings as $map) {
            $this->sync_listing($map->listing_id, $map->amelia_location_id);
        }
        
        error_log('ROVLEX Amelia Bridge: Синхронизация завершена. Обработано листингов: ' . count($mappings));
    }
    
    /**
     * Синхронизировать один листинг
     */
    private function sync_listing($listing_id, $amelia_location_id) {
        global $wpdb;
        
        // Валюта из Amelia или Listeo
        $currency = $this->get_currency();
        
        // ── 1. Получить Staff (Employees) для этой локации ──
        
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT u.id, u.firstName, u.lastName, u.email, u.phone,
                    u.pictureFullPath, u.pictureThumbPath,
                    u.description AS about
             FROM {$wpdb->prefix}amelia_users u
             INNER JOIN {$wpdb->prefix}amelia_providers_to_locations pl 
                ON u.id = pl.userId
             WHERE pl.locationId = %d
               AND u.type = 'provider'
               AND u.status = 'visible'
             ORDER BY u.firstName ASC",
            $amelia_location_id
        ));
        
        // ⚠️ ВАЖНО: Проверить что JOIN-таблица правильная
        // Альтернатива если нет providers_to_locations:
        // WHERE u.locationId = %d AND u.type = 'provider'
        
        // ── 2. Получить Services для этой локации ──
        
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT s.id, s.name, s.price, s.duration, s.description,
                    s.pictureFullPath, s.pictureThumbPath
             FROM {$wpdb->prefix}amelia_services s
             INNER JOIN {$wpdb->prefix}amelia_providers_to_services ps 
                ON s.id = ps.serviceId
             INNER JOIN {$wpdb->prefix}amelia_providers_to_locations pl 
                ON ps.userId = pl.userId
             WHERE pl.locationId = %d
               AND s.status = 'visible'
             GROUP BY s.id
             ORDER BY s.price ASC",
            $amelia_location_id
        ));
        
        // ⚠️ АЛЬТЕРНАТИВНЫЙ SQL если структура Amelia другая:
        // SELECT s.* FROM wp_amelia_services s
        // INNER JOIN wp_amelia_services_to_locations sl ON s.id = sl.serviceId
        // WHERE sl.locationId = %d AND s.status = 'visible'
        
        // ── 3. Сгенерировать HTML для Staff ──
        
        $staff_html = '';
        if (!empty($staff)) {
            foreach ($staff as $member) {
                $name = esc_html($member->firstName . ' ' . $member->lastName);
                $photo = $member->pictureThumbPath 
                    ? esc_url($member->pictureThumbPath) 
                    : ''; // placeholder будет в CSS
                $about = esc_html(wp_trim_words($member->about ?? '', 15));
                
                $staff_html .= '<div class="rovlex-staff-card">';
                if ($photo) {
                    $staff_html .= '<img src="' . $photo . '" alt="' . $name . '" class="staff-photo">';
                } else {
                    $staff_html .= '<div class="staff-photo" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:24px;color:#9ca3af;">👤</div>';
                }
                $staff_html .= '<div class="staff-name">' . $name . '</div>';
                if ($about) {
                    $staff_html .= '<div class="staff-position">' . $about . '</div>';
                }
                $staff_html .= '</div>';
            }
        }
        
        // ── 4. Сгенерировать HTML для Services ──
        
        $services_html = '';
        if (!empty($services)) {
            foreach ($services as $service) {
                $sname = esc_html($service->name);
                $price = $this->format_price($service->price, $currency);
                $duration = $this->format_duration($service->duration);
                
                $services_html .= '<div class="rovlex-service-item">';
                $services_html .= '<div class="rovlex-service-info">';
                $services_html .= '<div class="rovlex-service-name">' . $sname . '</div>';
                $services_html .= '<div class="rovlex-service-duration">' . $duration . '</div>';
                $services_html .= '</div>';
                $services_html .= '<div class="rovlex-service-price">' . $price . '</div>';
                $services_html .= '</div>';
            }
        }
        
        // ── 5. Сохранить в post_meta ──
        
        update_post_meta($listing_id, '_rovlex_staff_html', $staff_html);
        update_post_meta($listing_id, '_rovlex_services_html', $services_html);
        update_post_meta($listing_id, '_rovlex_staff_count', count($staff));
        update_post_meta($listing_id, '_rovlex_services_count', count($services));
        update_post_meta($listing_id, '_rovlex_last_sync', current_time('mysql'));
    }
    
    /**
     * Форматировать цену
     */
    private function format_price($price, $currency) {
        $symbols = [
            'GBP' => '£', 'EUR' => '€', 'USD' => '$',
            'PLN' => 'zł', 'CZK' => 'Kč'
        ];
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format(floatval($price), 2);
    }
    
    /**
     * Форматировать длительность
     */
    private function format_duration($minutes) {
        $minutes = intval($minutes);
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $mins > 0 
                ? "{$hours}h {$mins}min" 
                : "{$hours}h";
        }
        return "{$minutes} min";
    }
    
    /**
     * Получить валюту из настроек Amelia или Listeo
     */
    private function get_currency() {
        // Попробовать Amelia
        $amelia_settings = get_option('amelia_settings');
        if ($amelia_settings) {
            $settings = json_decode($amelia_settings, true);
            if (!empty($settings['payments']['currency'])) {
                return $settings['payments']['currency'];
            }
        }
        // Fallback на Listeo
        return get_option('listeo_currency', 'GBP');
    }
    
    /**
     * Ручная синхронизация через AJAX (для отладки)
     */
    public function ajax_force_sync() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Forbidden');
        }
        
        $this->run_sync();
        wp_send_json_success(['message' => 'Sync completed']);
    }
}

// Зарегистрировать кастомный интервал cron
add_filter('cron_schedules', function($schedules) {
    $schedules['fifteen_minutes'] = [
        'interval' => 900, // 15 минут
        'display'  => 'Every 15 minutes'
    ];
    return $schedules;
});
```

### Задача 4.2: Тестирование синхронизации

```bash
# Ручной запуск синхронизации
curl -s "https://rovlex.com/wp-admin/admin-ajax.php?action=rovlex_force_sync" \
  -H "Cookie: [admin_cookies]"

# Проверить что данные записались
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "SELECT post_id, meta_key, LEFT(meta_value, 100) 
      FROM wp_postmeta 
      WHERE meta_key LIKE '_rovlex_%' 
      ORDER BY post_id;"

# Проверить cron
wp cron event list --path=/path/to/rovlex.com | grep rovlex
```

**Критерий успеха Фазы 4:**
- [ ] Cron запускается каждые 15 минут
- [ ] Staff из Amelia появляются в `_rovlex_staff_html`
- [ ] Services из Amelia появляются в `_rovlex_services_html`
- [ ] HTML корректно отображается на странице листинга
- [ ] Изменения в Amelia (новый мастер/услуга) отражаются через 15 минут
- [ ] Ручная синхронизация через AJAX работает

---

## Фаза 5: Кнопка "Book Now" {#фаза-5}

### Задача 5.1: Добавить кнопку бронирования

**Файл:** `includes/class-listing-display.php`

```php
<?php
/**
 * Отображение кнопки "Book Now" на странице листинга
 * Редирект на форму бронирования Amelia
 */
class Rovlex_Listing_Display {

    public function __construct() {
        // Добавить кнопку "Book Now" на страницу листинга
        // ⚠️ ВАЖНО: Проверить правильный hook в шаблоне Listeo
        add_action('listeo_single_listing_after_content', [$this, 'render_book_button'], 20);
        
        // Альтернативный hook (если первый не сработает)
        add_action('listeo_sidebar_listing_actions', [$this, 'render_sidebar_book_button'], 10);
        
        // Создать страницу бронирования
        add_action('init', [$this, 'register_booking_page']);
        
        // Шорткод для страницы бронирования
        add_shortcode('rovlex_amelia_booking', [$this, 'booking_page_shortcode']);
    }
    
    /**
     * Кнопка "Book Now" в контенте листинга
     */
    public function render_book_button() {
        global $post;
        
        $amelia_location_id = get_post_meta($post->ID, '_amelia_location_id', true);
        if (!$amelia_location_id) return;
        
        $services_count = get_post_meta($post->ID, '_rovlex_services_count', true);
        if (!$services_count) return;
        
        $booking_url = $this->get_booking_url($amelia_location_id);
        
        echo '<div class="rovlex-booking-cta" style="text-align:center; padding: 20px 0;">';
        echo '<a href="' . esc_url($booking_url) . '" class="rovlex-book-btn">';
        echo '📅 Book an Appointment</a>';
        echo '</div>';
    }
    
    /**
     * Кнопка "Book Now" в сайдбаре
     */
    public function render_sidebar_book_button($listing_id = null) {
        if (!$listing_id) {
            global $post;
            $listing_id = $post->ID;
        }
        
        $amelia_location_id = get_post_meta($listing_id, '_amelia_location_id', true);
        if (!$amelia_location_id) return;
        
        $booking_url = $this->get_booking_url($amelia_location_id);
        
        echo '<a href="' . esc_url($booking_url) . '" class="rovlex-book-btn" ';
        echo 'style="display:block; text-align:center; margin: 10px 0;">';
        echo '📅 Book Now</a>';
    }
    
    /**
     * Сформировать URL страницы бронирования
     * 
     * Два варианта:
     * A) Отдельная страница с шорткодом Amelia
     * B) Прямой URL формы Amelia
     */
    private function get_booking_url($amelia_location_id) {
        // Вариант A: Отдельная страница (рекомендуется)
        $booking_page = get_page_by_path('book');
        if ($booking_page) {
            return add_query_arg('location', $amelia_location_id, 
                get_permalink($booking_page->ID));
        }
        
        // Вариант B: Fallback — страница с шорткодом
        return home_url('/book/?location=' . $amelia_location_id);
    }
    
    /**
     * Шорткод для страницы бронирования
     * 
     * Создать страницу /book/ с содержимым: [rovlex_amelia_booking]
     */
    public function booking_page_shortcode($atts) {
        $location_id = isset($_GET['location']) ? intval($_GET['location']) : 0;
        
        if (!$location_id) {
            return '<p>Please select a salon first.</p>';
        }
        
        // Вывести шорткод Amelia с фильтрацией по локации
        // ⚠️ ВАЖНО: Проверить точный формат шорткода Amelia
        // Возможные варианты:
        // [ameliabooking location=X]
        // [ameliacatalog location=X]
        // [ameliastepbooking location=X]
        
        $output = '<div class="rovlex-booking-page">';
        $output .= do_shortcode('[ameliabooking location="' . $location_id . '"]');
        $output .= '</div>';
        
        return $output;
    }
    
    /**
     * Автоматически создать страницу /book/ при активации плагина
     */
    public function register_booking_page() {
        // Проверить что страница /book/ существует
        $page = get_page_by_path('book');
        if (!$page) {
            // Создать только один раз
            if (get_option('rovlex_booking_page_created')) return;
            
            wp_insert_post([
                'post_title'   => 'Book an Appointment',
                'post_name'    => 'book',
                'post_content' => '[rovlex_amelia_booking]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
            
            update_option('rovlex_booking_page_created', true);
        }
    }
}
```

### Задача 5.2: Создать страницу бронирования

Вручную (или автоматически через код выше):

1. WordPress Admin → Pages → Add New
2. Title: "Book an Appointment"
3. Slug: `book`
4. Content: `[rovlex_amelia_booking]`
5. Publish

**Критерий успеха Фазы 5:**
- [ ] На каждом листинге с Amelia-локацией есть кнопка "Book Now"
- [ ] Кнопка ведёт на `/book/?location=X`
- [ ] На странице `/book/` отображается форма Amelia с фильтрацией по локации
- [ ] В форме бронирования видны: мастера, услуги, расписание, цены

---

## Фаза 6: Тестирование {#фаза-6}

### Тест-сценарий 1: Создание листинга (Owner Flow)

| Шаг | Действие | Ожидаемый результат |
|-----|----------|---------------------|
| 1 | Овнер заходит на `/add-listing/` | Форма Listeo открывается |
| 2 | Заполняет: название, адрес, телефон, описание, фото | Все поля валидируются |
| 3 | Нажимает "Submit" | Листинг создан |
| 4 | — | Автоматически создаётся Amelia Location |
| 5 | — | В `_amelia_location_id` записан ID |
| 6 | Овнер перенаправлен в wp-admin Amelia | Видит уведомление |
| 7 | Добавляет 2 мастера в Amelia | Мастера сохранены с Location |
| 8 | Добавляет 3 услуги в Amelia | Услуги привязаны к мастерам |
| 9 | Ждёт 15 мин (или ручная синхронизация) | — |
| 10 | Открывает страницу листинга | Видит секцию Staff & Services |

### Тест-сценарий 2: Бронирование (Customer Flow)

| Шаг | Действие | Ожидаемый результат |
|-----|----------|---------------------|
| 1 | Клиент открывает страницу листинга | Видит информацию о салоне |
| 2 | Видит секцию "Staff & Services" | Карточки мастеров + список услуг с ценами |
| 3 | Нажимает "Book Now" | Редирект на `/book/?location=X` |
| 4 | Видит форму Amelia | Отфильтрована по этой локации |
| 5 | Выбирает услугу, мастера, дату/время | Всё работает |
| 6 | Заполняет контакты, подтверждает | Бронирование создано |

### Проверки через CLI

```bash
# 1. Проверить что плагин активен
wp plugin list --status=active --path=/path/to | grep rovlex-amelia

# 2. Проверить маппинг таблицу
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "SELECT * FROM wp_rovlex_amelia_map;"

# 3. Проверить post_meta
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "SELECT post_id, meta_key, LEFT(meta_value, 80) as value
      FROM wp_postmeta 
      WHERE meta_key IN ('_amelia_location_id', '_rovlex_staff_count', 
                          '_rovlex_services_count', '_rovlex_last_sync');"

# 4. Проверить cron
wp cron event list --path=/path/to | grep rovlex

# 5. Проверить логи
tail -50 /path/to/rovlex.com/wp-content/debug.log | grep -i rovlex
```

---

## 📁 Итоговая файловая структура

```
wp-content/plugins/rovlex-amelia-bridge/
├── rovlex-amelia-bridge.php              ← Главный файл плагина
├── includes/
│   ├── class-location-sync.php           ← Фаза 1: auto-create Location
│   ├── class-data-sync.php               ← Фаза 4: cron синхронизация
│   ├── class-listing-display.php         ← Фаза 5: Book Now + booking page
│   └── class-admin-redirect.php          ← Фаза 2: redirect после создания
└── README.md

wp-content/themes/listeo-child/
├── functions.php                         ← Фаза 3: кастомная секция (добавить код)
└── style.css                             ← Фаза 3: стили карточек (добавить код)

Страницы WordPress:
├── /book/                                ← Фаза 5: страница бронирования
│   └── content: [rovlex_amelia_booking]
```

---

## ⚠️ Что нужно верифицировать перед началом кодирования

Эти вещи невозможно точно определить без доступа к серверу — **первым делом нужно проверить:**

| # | Что проверить | Команда | Влияет на |
|---|---------------|---------|-----------|
| 1 | Точный hook Listeo при submit листинга | `grep -rn "do_action.*submit\|listing_saved" themes/listeo/` | Фаза 1, 2 |
| 2 | Meta-ключи Listeo (адрес, телефон, координаты) | `wp post meta list <ID>` | Фаза 1 |
| 3 | Структура таблицы `wp_amelia_locations` | `DESCRIBE wp_amelia_locations` | Фаза 1 |
| 4 | Связь providers ↔ locations в Amelia | `SHOW TABLES LIKE '%amelia%'` | Фаза 4 |
| 5 | Шорткоды Amelia с параметром location | Документация Amelia | Фаза 5 |
| 6 | Фильтр редиректа Listeo после submit | `grep -rn "redirect" themes/listeo/includes/` | Фаза 2 |
| 7 | Hooks для кастомных секций на странице листинга | `grep -rn "do_action.*single.*listing" themes/listeo/` | Фаза 3 |

---

## 🕐 Оценка времени

| Фаза | Задачи | Время |
|------|--------|-------|
| 0 | Подготовка, верификация | 1-2 часа |
| 1 | Auto-create Amelia Location | 2-3 часа |
| 2 | Редирект в Amelia | 1-2 часа |
| 3 | Кастомные поля Listeo | 2-3 часа |
| 4 | Синхронизация (cron) | 3-4 часа |
| 5 | Book Now + booking page | 1-2 часа |
| 6 | Тестирование + отладка | 2-3 часа |
| **ИТОГО** | | **12-19 часов** |

---

**Документ создан:** 6 февраля 2026  
**Для:** Claude Code / разработчик  
**Сайт:** rovlex.com  
**Статус:** Готов к реализации после верификации (Фаза 0)
