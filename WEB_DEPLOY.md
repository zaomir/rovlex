# ROVLEX Amelia Bridge - Web Deployment (No SSH Required)

## ⚡ Fastest Method: Direct PHP Execution

Since SSH is currently unavailable, use the web-based deployment:

### Step 1: Upload auto-deploy.php

**Option A: Via ISPmanager**
- Login to https://213.155.28.121:8443/ (ISPmanager)
- File Manager → navigate to public_html
- Upload `auto-deploy.php` from the repo

**Option B: Via FTP**
- Connect to 213.155.28.121 with FTP
- Navigate to `/var/www/rovlex.com/public_html/`
- Upload `auto-deploy.php`

**Option C: Via Direct File Upload**
```bash
# Copy to your local machine first
scp /home/user/rovlex/auto-deploy.php donnyduck@[your-machine]:/downloads/

# Then upload via FTP or ISPmanager web interface
```

### Step 2: Execute Deployment

Open in browser:
```
https://rovlex.com/auto-deploy.php?key=install
```

**OR via curl if you have CLI access:**
```bash
curl "https://rovlex.com/auto-deploy.php?key=install"
```

### Step 3: Wait for Completion

The script will:
- ✅ Create plugin directory structure
- ✅ Create all plugin files
- ✅ Create database table
- ✅ Activate plugin
- ✅ Create booking page `/book/`
- ✅ Run verification checks
- ✅ Display deployment status

### Step 4: Verify Success

After deployment completes, check:

```bash
# Check plugin is active
curl -s https://rovlex.com/wp-json/wp/v2/plugins | grep rovlex

# Check plugin status via wp-cli (if available)
wp rovlex status --allow-root

# Check booking page exists
curl -s https://rovlex.com/book/ | grep -q "Book an Appointment" && echo "OK" || echo "MISSING"
```

### Step 5: Clean Up

Delete the deployment script:
- Via ISPmanager: File Manager → delete `auto-deploy.php`
- Or the script will show cleanup instructions

## 📋 What Gets Deployed

### Files Created:
```
wp-content/plugins/rovlex-amelia-bridge/
├── rovlex-amelia-bridge.php           (main plugin)
├── includes/
│   ├── class-location-sync.php        (Phase 1)
│   ├── class-admin-redirect.php       (Phase 2)
│   ├── class-data-sync.php            (Phase 4)
│   └── class-listing-display.php      (Phase 5)
└── assets/
    └── styles.css                     (styling)
```

### Database:
```sql
CREATE TABLE wp_rovlex_amelia_map (
    id INT,
    listing_id INT,
    amelia_location_id INT,
    created_at TIMESTAMP
);
```

### Pages Created:
```
POST /book/
  Title: "Book an Appointment"
  Content: [rovlex_amelia_booking]
```

### WP Cron:
```
Event: rovlex_amelia_sync
Interval: Every 15 minutes
```

## 🧪 Testing After Deployment

### Test Phase 1 (Location Creation)

```bash
# Create a test listing
wp post create --post_type=listing \
  --post_title="Test Salon" \
  --post_content="Test description" \
  --post_status=publish \
  --allow-root

# Check if Amelia Location was created
wp rovlex test 1 --allow-root
```

### Test Phase 4 (Sync)

```bash
# Force sync
wp rovlex sync force --allow-root

# Check if staff/services populated
wp rovlex test 3 --allow-root
```

### Test Phase 5 (Booking)

```bash
# Visit the booking page
curl https://rovlex.com/book/?location=21

# Check button on listing page
curl https://rovlex.com/some-listing/ | grep -i "book now"
```

## 🆘 Troubleshooting

### Script shows "Access denied"
- Make sure you use correct key: `?key=install`
- Check that auto-deploy.php is in public_html root

### Plugin doesn't activate
- Enable debug: Add to wp-config.php
  ```php
  define('WP_DEBUG', true);
  define('WP_DEBUG_LOG', true);
  ```
- Check: `/wp-content/debug.log`

### Database table not created
- Run script again
- Or manually create:
  ```bash
  wp db query "CREATE TABLE wp_rovlex_amelia_map (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNIQUE,
    amelia_location_id INT UNIQUE
  );" --allow-root
  ```

### Booking page not created
- Create manually:
  ```bash
  wp post create --post_type=page \
    --post_title="Book an Appointment" \
    --post_name=book \
    --post_content='[rovlex_amelia_booking]' \
    --post_status=publish \
    --allow-root
  ```

## 📞 Support

If web deployment doesn't work:

1. **Check site is accessible**: `curl -I https://rovlex.com/`
2. **Check WordPress is working**: `curl https://rovlex.com/wp-admin/ | head -20`
3. **Check PHP version**: `curl https://rovlex.com/wp-admin/ | grep "PHP"`
4. **Check permissions**: Files should be readable by www-data user

## 🎯 Final Check

After deployment, all phases should work:

```
✅ Phase 1: Listings auto-create Amelia Locations
✅ Phase 2: Owner redirected to Amelia admin
✅ Phase 3: Staff & Services displayed on listings
✅ Phase 4: Data syncs every 15 minutes
✅ Phase 5: "Book Now" button works
```

---

**Deployment Date**: 2026-02-06
**Method**: Web-based (No SSH)
**Status**: Ready
