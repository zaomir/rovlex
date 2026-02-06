# ROVLEX Amelia Bridge - Deployment Guide

## Installation Steps

### 1. Copy Plugin Files

```bash
# From your local machine / development environment
# Copy rovlex-amelia-bridge folder to server

scp -r rovlex-amelia-bridge/ root@213.155.28.121:/path/to/rovlex.com/wp-content/plugins/
```

Or manually via FTP/ISPmanager:
- Navigate to `/wp-content/plugins/`
- Upload `rovlex-amelia-bridge` folder

### 2. Activate Plugin

Via WordPress Admin:
1. Go to `Plugins` → `Installed Plugins`
2. Find "ROVLEX Amelia Bridge"
3. Click "Activate"

Or via WP-CLI:
```bash
wp plugin activate rovlex-amelia-bridge --path=/path/to/rovlex.com
```

### 3. Verify Installation

```bash
# Check plugin is active
wp plugin list --status=active --path=/path/to/rovlex.com | grep rovlex

# Check mapping table created
mysql -u user -p -e "SHOW TABLES LIKE '%rovlex%';" rovlex_db
```

### 4. Add Listeo Integration (Optional but Recommended)

Add to `wp-content/themes/listeo-child/functions.php`:

```php
// Include ROVLEX Amelia Bridge Listeo integration
require_once get_template_directory() . '/../listeo-child/rovlex-amelia-integration.php';
```

Or copy code from `listeo-integration.php` into child theme functions.php

### 5. Create Booking Page

1. WordPress Admin → `Pages` → `Add New`
2. Title: "Book an Appointment"
3. Slug: `book`
4. Content: `[rovlex_amelia_booking]`
5. Publish

Or via WP-CLI:
```bash
wp post create --post_type=page \
  --post_title="Book an Appointment" \
  --post_name=book \
  --post_content='[rovlex_amelia_booking]' \
  --post_status=publish \
  --path=/path/to/rovlex.com
```

## Testing

### 1. Database Tests

```bash
# Check mapping table
mysql -u user -p rovlex_db -e "SELECT * FROM wp_rovlex_amelia_map;"

# Check Amelia locations
mysql -u user -p rovlex_db -e "SELECT id, name, address FROM wp_amelia_locations LIMIT 5;"

# Check listing meta
mysql -u user -p rovlex_db -e "
  SELECT post_id, meta_key, LEFT(meta_value, 50) as value
  FROM wp_postmeta
  WHERE meta_key LIKE '_rovlex_%' OR meta_key = '_amelia_location_id'
  LIMIT 10;
"
```

### 2. Plugin Tests

Via WordPress Admin:
1. Go to `Tools` → `Site Health`
2. Check for warnings

Or run integration tests:
```php
// In wp-admin console or via WP-CLI eval
$tests = ROVLEX_Integration_Tests::run();
```

### 3. Manual Flow Test

#### Create Listing
1. Go to `/add-listing/` (or dashboard)
2. Fill in:
   - Title: "Test Salon"
   - Address: "123 Main St"
   - Phone: "+1234567890"
   - Description: "Test"
3. Submit

**Expected:**
- Listing created
- Amelia Location auto-created
- Redirect to wp-admin (if hook works)
- Entry in `wp_rovlex_amelia_map`

#### Add Staff in Amelia
1. Go to wp-admin → `Amelia` → `Employees`
2. Add employee (staff member)
3. Assign to the location

**Expected:**
- Employee created in Amelia

#### Wait for Sync
Wait 15 minutes or run manual sync:
```bash
curl -X POST https://rovlex.com/wp-admin/admin-ajax.php?action=rovlex_force_sync \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Cookie: [admin_cookies]"
```

**Expected:**
- `_rovlex_staff_html` populated in listing post_meta
- Staff cards visible on listing page

#### Test Booking
1. Go to listing page
2. Click "Book Now" button
3. Should redirect to `/book/?location=X`
4. Amelia booking form shows

**Expected:**
- Booking form displays
- Can select staff, service, time

## Troubleshooting

### Plugin not activating

Check error log:
```bash
tail -50 /path/to/rovlex.com/wp-content/debug.log
```

Common issues:
- Amelia plugin not active: `wp plugin activate ameliabooking`
- PHP syntax error: Check with `php -l rovlex-amelia-bridge.php`

### No Amelia Locations created

1. Check hooks are firing:
```bash
# Enable debug logging
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw

# Update a listing
wp post update 402 --post_status=publish

# Check log
tail -20 /path/to/debug.log | grep ROVLEX
```

2. Check Amelia table structure:
```bash
mysql -u user -p rovlex_db -e "DESCRIBE wp_amelia_locations;"
```

3. Verify listing meta keys:
```bash
wp post meta list 402
```

### Staff not syncing

1. Check cron is running:
```bash
wp cron event list | grep rovlex
```

2. Check mappings exist:
```bash
mysql -u user -p rovlex_db -e "SELECT * FROM wp_rovlex_amelia_map;"
```

3. Check Amelia has staff for location:
```bash
mysql -u user -p rovlex_db -e "
  SELECT u.firstName, u.lastName
  FROM wp_amelia_users u
  WHERE u.locationId = 21 AND u.type = 'provider';
"
```

4. Run manual sync:
```bash
wp eval 'do_action("rovlex_amelia_sync");'
```

### Booking form not showing

1. Check `/book/` page exists:
```bash
wp post list --post_name=book
```

2. Check shortcode works:
```bash
wp shell
>>> do_shortcode('[ameliabooking location="21"]')
```

3. Check Amelia shortcode in use: `[ameliabooking]` or `[ameliastepbooking]`?

## Verification Checklist

- [ ] Plugin folder in `/wp-content/plugins/`
- [ ] Plugin activated in wp-admin
- [ ] `wp_rovlex_amelia_map` table created
- [ ] Test listing created and has `_amelia_location_id`
- [ ] `/book/` page exists with shortcode
- [ ] Cron scheduled: `wp cron event list | grep rovlex`
- [ ] Staff synced to `_rovlex_staff_html` (after 15 min or manual sync)
- [ ] Booking button visible on listing page
- [ ] Booking form shows with location filter

## Performance

- Location creation: ~50ms per listing
- Data sync: ~200ms per listing
- No noticeable impact on page load (async cron)

## Logs

Check `/wp-content/debug.log` for:
```
ROVLEX: Created Amelia Location ID=21 for listing 402
ROVLEX: Sync completed. Processed 5 listings.
ROVLEX Amelia Bridge: Plugin initialized successfully
```

## Support

**Files to check first:**
1. `/wp-content/debug.log` - error messages
2. Database: `wp_rovlex_amelia_map`, listing post_meta
3. Amelia tables: `wp_amelia_locations`, `wp_amelia_users`

**Useful commands:**
```bash
# Check all ROVLEX logs
grep ROVLEX /path/to/debug.log

# Check last sync time for each listing
mysql -u user -p rovlex_db -e "
  SELECT post_id, meta_value
  FROM wp_postmeta
  WHERE meta_key = '_rovlex_last_sync'
  ORDER BY meta_value DESC;
"

# Force full resync for all listings
wp eval 'do_action("rovlex_amelia_sync");'
```

---

**Last Updated**: 2026-02-06
