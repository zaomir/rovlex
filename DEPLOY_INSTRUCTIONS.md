# ROVLEX Amelia Bridge - Развертывание на сервер

## Быстрый старт (2-3 минуты)

### Опция A: Через архив + Скрипт (Рекомендуется)

**На вашем Mac:**

```bash
# 1. Загрузить архив плагина на сервер
scp rovlex-amelia-bridge.tar.gz donnyduck@213.155.28.121:~/

# 2. Загрузить скрипт развертывания
scp deploy.sh root@213.155.28.121:~/
```

**На сервере (SSH):**

```bash
# 1. Распаковать архив
cd ~/
tar -xzf rovlex-amelia-bridge.tar.gz

# 2. Запустить скрипт развертывания
bash deploy.sh

# 3. Проверить статус
wp rovlex status --allow-root
```

### Опция B: Через Git (Если Git установлен на сервере)

**На сервере:**

```bash
# 1. Перейти в папку плагинов
cd /var/www/rovlex.com/public_html/wp-content/plugins/

# 2. Клонировать репозиторий
git clone https://github.com/... rovlex-amelia-bridge
cd rovlex-amelia-bridge
git checkout claude/read-integration-plan-t56To

# 3. Активировать
wp plugin activate rovlex-amelia-bridge --allow-root
```

### Опция C: Вручную через FTP/ISPmanager

1. Загрузить архив в `/wp-content/plugins/`
2. Распаковать: `unzip rovlex-amelia-bridge.zip`
3. В wp-admin: Plugins → Activate "ROVLEX Amelia Bridge"
4. Создать страницу `/book/` с кодом: `[rovlex_amelia_booking]`

## Пошаговое развертывание

### Шаг 1: Загрузить файлы плагина

Выбери один из способов выше.

### Шаг 2: Активировать плагин

```bash
# Через wp-cli
wp plugin activate rovlex-amelia-bridge --allow-root

# Или вручную в wp-admin:
# Plugins → Install Plugins → Upload → Choose File
```

### Шаг 3: Проверить установку

```bash
# Проверить активен ли плагин
wp plugin list --status=active --allow-root | grep rovlex

# Проверить состояние интеграции
wp rovlex status --allow-root

# Ожидаемый вывод:
# Plugin Active: ✅ Yes
# Mapping Table: ✅ Exists
# Mappings: X location(s)
# Next Cron: [date/time]
```

### Шаг 4: Создать страницу бронирования

```bash
# Через wp-cli
wp post create --post_type=page \
  --post_title="Book an Appointment" \
  --post_name=book \
  --post_content='[rovlex_amelia_booking]' \
  --post_status=publish \
  --allow-root

# Или вручную в wp-admin:
# Pages → Add New
# Title: "Book an Appointment"
# Slug: book
# Content: [rovlex_amelia_booking]
# Publish
```

### Шаг 5: Протестировать интеграцию

```bash
# Запустить все тесты
wp rovlex test all --allow-root

# Проверить каждую фазу отдельно
wp rovlex test 1    # Location creation
wp rovlex test 2    # Admin redirect
wp rovlex test 3    # Display
wp rovlex test 4    # Cron sync
wp rovlex test 5    # Booking
```

## Тестирование после развертывания

### Тест 1: Создание листинга (Phase 1)

```bash
# 1. Создать тестовый листинг
wp post create --post_type=listing \
  --post_title="Test Salon" \
  --post_content="Test description" \
  --post_status=publish \
  --meta_input='{"_address":"123 Main St","_phone":"+1234567890"}' \
  --allow-root

# 2. Проверить что Amelia Location создана
wp rovlex test 1 --allow-root
```

### Тест 2: Синхронизация (Phase 4)

```bash
# 1. Ручная синхронизация
wp rovlex sync force --allow-root

# 2. Проверить что данные синхронизированы
wp rovlex test 3 --allow-root  # Staff & Services

# 3. Проверить базу
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ << EOF
  SELECT post_id, meta_key, LENGTH(meta_value) as size
  FROM wp_postmeta
  WHERE meta_key IN ('_rovlex_staff_html', '_rovlex_services_html')
  LIMIT 5;
EOF
```

### Тест 3: Страница бронирования (Phase 5)

```bash
# 1. Проверить что страница существует
wp post list --post_name=book --allow-root

# 2. Открыть в браузере
# https://rovlex.com/book/?location=21
# Должна быть форма бронирования Amelia

# 3. Проверить на обычном листинге
# https://rovlex.com/-/390/
# Должна быть кнопка "Book Now"
```

## Общие команды для управления

```bash
# Показать статус интеграции
wp rovlex status --allow-root

# Запустить все тесты
wp rovlex test all --allow-root

# Ручная синхронизация
wp rovlex sync force --allow-root

# Проверить логи
tail -100 /var/www/rovlex.com/public_html/wp-content/debug.log | grep ROVLEX

# Показать активные плагины
wp plugin list --status=active --allow-root

# Деактивировать плагин (если нужно)
wp plugin deactivate rovlex-amelia-bridge --allow-root
```

## Troubleshooting при развертывании

### Плагин не активируется

```bash
# 1. Проверить синтаксис PHP
php -l rovlex-amelia-bridge.php

# 2. Проверить что Amelia установлена
wp plugin list --status=active --allow-root | grep amelia

# 3. Включить debug
wp config set WP_DEBUG true --raw --allow-root
wp config set WP_DEBUG_LOG true --allow-root
wp config set WP_DEBUG_DISPLAY false --raw --allow-root

# 4. Проверить ошибки
tail -50 /var/www/rovlex.com/public_html/wp-content/debug.log
```

### Таблица БД не создана

```bash
# Проверить таблицу
mysql -u test_rovlex_ -p':f:39vBcA?Ut}&uu' test_rovlex_ \
  -e "SHOW TABLES LIKE 'wp_rovlex_amelia_map';"

# Если таблица есть - ОК
# Если нет - переактивировать плагин:
wp plugin deactivate rovlex-amelia-bridge --allow-root
wp plugin activate rovlex-amelia-bridge --allow-root
```

### WP-CLI команды не работают

```bash
# Проверить что wp-cli установлен
which wp

# Если нет, установить
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Проверить версию
wp --version
```

### Cron не запускается

```bash
# Проверить что cron задача зарегистрирована
wp cron event list --allow-root | grep rovlex

# Если нет - переактивировать плагин
wp plugin deactivate rovlex-amelia-bridge --allow-root
wp plugin activate rovlex-amelia-bridge --allow-root

# Проверить снова
wp cron event list --allow-root | grep rovlex
```

## После успешного развертывания

✅ Плагин установлен и активирован
✅ Таблица маппинга создана
✅ Страница `/book/` существует
✅ Cron задача зарегистрирована
✅ Все тесты проходят

**Готово к использованию!**

- Овнеры могут создавать листинги
- Amelia Locations создаются автоматически
- Данные синхронизируются каждые 15 минут
- Клиенты могут бронировать через "Book Now"

## Поддержка

**Для быстрой диагностики:**

```bash
wp rovlex test all --allow-root
wp rovlex status --allow-root
```

**Для подробных логов:**

```bash
tail -100 /var/www/rovlex.com/public_html/wp-content/debug.log | grep -i rovlex
```

**Для ручной синхронизации:**

```bash
wp rovlex sync force --allow-root
```

---

**Дата**: 2026-02-06
**Версия плагина**: 1.0.0
**Статус**: Готов к развертыванию
