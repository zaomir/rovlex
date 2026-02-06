# ROVLEX Amelia Bridge - Quick Start

## 5-Minute Setup

### 1. Install Plugin
```bash
# Copy to server
cd /path/to/rovlex.com/wp-content/plugins/
git clone [repo] rovlex-amelia-bridge
# OR download zip and extract
```

### 2. Activate
```bash
wp plugin activate rovlex-amelia-bridge
```

### 3. Check Status
```bash
wp rovlex status
```

Expected output:
```
Plugin Active: ✅ Yes
Mapping Table: ✅ Exists
Mappings: X location(s)
Next Cron: [date/time]
```

### 4. Create Booking Page
```bash
wp post create --post_type=page \
  --post_title="Book an Appointment" \
  --post_name=book \
  --post_content='[rovlex_amelia_booking]' \
  --post_status=publish
```

### 5. Test Flow

**Test Phase 1-2 (Create Listing):**
```bash
wp post create --post_type=listing \
  --post_title="Test Salon" \
  --post_content='Test description' \
  --post_status=publish \
  --meta_input='{"_address":"123 Main St","_phone":"+1234567890"}'

# Check it created Location
wp rovlex test 1
```

**Test Phase 4 (Sync):**
```bash
# Wait 15 minutes OR run manual sync
wp rovlex sync force

# Check if staff/services populated
wp rovlex test 3
```

**Test Phase 5 (Booking):**
```bash
# Visit listing page
# Should have "Book Now" button
# Click it → goes to /book/?location=X
```

## Common Commands

```bash
# Run all tests
wp rovlex test all

# Test specific phase
wp rovlex test 1    # Location creation
wp rovlex test 4    # Cron sync

# Manual sync
wp rovlex sync force

# Check status
wp rovlex status

# View debug logs
tail -50 /path/to/wp-content/debug.log | grep ROVLEX

# Check database
mysql -u user -p db << EOF
  SELECT COUNT(*) as mappings FROM wp_rovlex_amelia_map;
  SELECT COUNT(*) as listings WITH staff FROM wp_postmeta
    WHERE meta_key='_rovlex_staff_html' AND meta_value != '';
EOF
```

## Troubleshooting

**Plugin not activating?**
```bash
wp plugin activate rovlex-amelia-bridge
# Check error output
```

**No locations created?**
```bash
# Enable debug
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw

# Create test listing
wp post create --post_type=listing --post_title="Test" --post_status=publish

# Check log
tail -20 /path/to/debug.log | grep ROVLEX
```

**Staff not syncing?**
```bash
# Check cron is scheduled
wp cron event list | grep rovlex

# Run manual sync
wp rovlex sync force

# Check Amelia has staff for location
mysql -u user -p db -e "
  SELECT * FROM wp_amelia_users
  WHERE locationId=21 AND type='provider';
"
```

## Files

```
rovlex-amelia-bridge.php          Main plugin
├── includes/
│   ├── class-location-sync.php   Phase 1
│   ├── class-admin-redirect.php  Phase 2
│   ├── class-listing-display.php Phase 3 & 5
│   └── class-data-sync.php       Phase 4
├── assets/styles.css             Styling
├── tests/
│   ├── test-integration.php       Test suite
│   └── wp-cli-test.php            WP-CLI commands
├── listeo-integration.php         Add to child theme
├── README.md                      Full docs
├── DEPLOYMENT.md                  Deployment guide
└── QUICKSTART.md                  This file
```

## Next Steps

1. ✅ Plugin installed and activated
2. ✅ Booking page created
3. ✅ Database setup complete

**Ready to test!**

- Create a listing → should auto-create Amelia Location
- Add staff in Amelia → should sync to listing
- Click "Book Now" → Amelia form with location filter

---

**Support**: Check debug.log, run `wp rovlex test all`
