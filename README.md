# ROVLEX Amelia Bridge

Integration plugin for Amelia Booking + Listeo Marketplace for rovlex.com

## Overview

This plugin automates the integration between Amelia (booking system) and Listeo (marketplace listings):
- **Phase 1**: Auto-create Amelia Location when listing created
- **Phase 2**: Redirect owner to Amelia admin after creation
- **Phase 3**: Display staff & services on listing pages
- **Phase 4**: Sync data from Amelia every 15 minutes
- **Phase 5**: "Book Now" button with location filter

## Installation

1. Copy folder to `wp-content/plugins/`
2. Activate plugin
3. Ensure Amelia Booking is active

## Database

Creates table: `wp_rovlex_amelia_map`
```
listing_id ↔ amelia_location_id
```

## Post Meta Keys

- `_amelia_location_id` - Amelia Location ID
- `_rovlex_staff_html` - Staff section HTML
- `_rovlex_services_html` - Services section HTML
- `_rovlex_last_sync` - Last sync time

## Cron

Event: `rovlex_amelia_sync` (every 15 min)

Manual trigger:
```
curl -X POST https://rovlex.com/wp-admin/admin-ajax.php?action=rovlex_force_sync
```

## Shortcodes

`[rovlex_amelia_booking]` - Booking form with location filter

## Logs

All operations logged to debug.log with ROVLEX prefix

## Security

- Escaped output
- Prepared SQL statements
- Capability checks
