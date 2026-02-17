# ROVLEX.COM — Site Summary (2026-02-17)

## 1. Server & WordPress

| Parameter       | Value                          |
|-----------------|--------------------------------|
| Domain          | rovlex.com                     |
| VDS IP          | 213.155.28.121 (test.rovlex.com) |
| PHP             | 8.1.2                         |
| MySQL           | 8.0.45                        |
| WordPress       | 6.9.1 (en_GB)                 |
| Timezone        | Europe/Paris                   |
| Front Page      | Page ID 122 — "Rovlex Home"   |
| Blog Page       | Page ID 135 — "Blog"          |
| Admin Email     | wsc8eq@gmail.com               |
| Active Theme    | Listeo ChildTheme v1.0 (parent: Listeo v2.0.19) |

---

## 2. Users

| ID | Name               | Slug                 | Roles              |
|----|--------------------|----------------------|---------------------|
| 1  | Rovlex             | rovlex               | Super Admin         |
| 3  | Bebo Master        | bebomybebo-fr        | Vendor (Dokan)      |
| 4  | diroco@diroco.com  | dirocodiroco-com     | (subscriber/vendor) |

---

## 3. Active Plugins (27 total)

### Core Platform
| Plugin                        | Version | Purpose                               |
|-------------------------------|---------|---------------------------------------|
| WooCommerce                   | 10.4.3  | E-commerce engine                     |
| Dokan Lite                    | 4.2.10  | Multi-vendor marketplace              |
| Elementor                     | 3.35.4  | Page builder                          |
| Polylang                      | 3.7.7   | Multilingual (i18n)                   |

### Listings (Listeo Stack)
| Plugin                        | Version | Purpose                               |
|-------------------------------|---------|---------------------------------------|
| Listeo-Core                   | 2.0.19  | Directory / listings engine           |
| Listeo Forms & Fields Editor  | 2.0.15  | Custom listing fields                 |
| Listeo Elementor              | 2.0.11  | Elementor widgets for Listeo          |
| Listeo Shortcodes             | 1.5.22  | Shortcodes for listings               |
| PureCustomizer Framework      | 1.0.0   | Theme customizer                      |
| Purethemes.net CPT            | 1.3     | Custom post types                     |

### Booking (Amelia)
| Plugin                        | Version | Purpose                               |
|-------------------------------|---------|---------------------------------------|
| Amelia                        | 8.7     | Appointment booking system            |
| **ROVLEX Amelia Bridge**      | 1.0.1   | Amelia ↔ Listeo data sync             |
| **ROVLEX Amelia Integration** | 1.0.0   | Amelia ↔ Listeo frontend integration  |

### Utilities
| Plugin                        | Version | Purpose                               |
|-------------------------------|---------|---------------------------------------|
| AI Chat & Search              | 1.8.6   | AI-powered search                     |
| Autocomplete WooCommerce Orders | 3.5.5 | Auto-complete orders                  |
| Breadcrumb NavXT              | 7.5.1   | Breadcrumb navigation                 |
| CMB2                          | 2.11.0  | Custom meta boxes                     |
| CMB2 Field Slider             | 1.1.2   | CMB2 slider field extension           |
| Contact Form 7                | 6.1.4   | Forms                                 |
| CF7 Dynamic Text Extension    | 5.0.4   | Dynamic CF7 fields                    |
| Copy & Delete Posts           | 1.5.2   | Duplicate posts                       |
| Say What?                     | 2.2.5   | String replacements                   |
| WP Mail SMTP                  | 4.7.1   | SMTP mail delivery                    |

### Inactive
| Plugin                        | Version |
|-------------------------------|---------|
| Akismet Anti-spam             | 5.6     |
| Bookly                        | 26.1    |
| Bookly Locations (Add-on)     | 6.2     |
| Bookly Pro (Add-on)           | 9.5     |

---

## 4. Dokan Stores

| ID | Store Name | Enabled |
|----|------------|---------|
| 1  | (Rovlex)   | Yes     |

Only 1 active store so far. Dokan is set up for multi-vendor marketplace.

---

## 5. Listing Categories (listing_category)

| ID  | Category         | Listings |
|-----|------------------|----------|
| 307 | Beauty Masters   | 3        |
| 310 | Hair             | 0        |
| 311 | Makeup           | 0        |
| 309 | Nails            | 0        |
| 312 | Eyebrows & Lashes | 0      |
| 313 | Skincare         | 0        |
| 314 | Hair Removal     | 0        |
| 315 | Spa & Massage    | 0        |
| 316 | PMU              | 0        |
| 308 | Handymen         | 0        |
| 317 | Plumbing         | 0        |
| 318 | Electrical       | 0        |
| 319 | General Repair   | 0        |
| 320 | Cleaning         | 0        |
| 321 | Installation     | 0        |
| 322 | Furniture        | 0        |
| 323 | Doors & Windows  | 0        |

**Primary niche: Beauty / Services marketplace** — categories span beauty services and home services.

---

## 6. Listings (first 20)

| ID  | Title     | Category       |
|-----|-----------|----------------|
| 458 | saloon44  | Beauty Masters |
| 402 | 390       | test           |
| 401 | цв        | test           |
| 399 | 3         | test           |
| 398 | 45        | test           |
| 397–389 | test entries | test     |
| 385 | saloon 7  | Beauty Masters |
| 384 | saloon6   | Beauty Masters |
| 383 | saloon5   | —              |
| 381 | saloon4   | —              |
| 378 | saloon3   | —              |
| 376 | Saloon2   | —              |

Most listings are test entries. **saloon44** (ID:458) is the most complete.

---

## 7. Amelia Database (Full Dump)

### 7.1 Amelia Tables (48 tables, prefix: wp_amelia_)

`wp_amelia_appointments`, `wp_amelia_cache`, `wp_amelia_categories`, `wp_amelia_coupons`, `wp_amelia_coupons_to_events`, `wp_amelia_coupons_to_packages`, `wp_amelia_coupons_to_services`, `wp_amelia_custom_fields`, `wp_amelia_custom_fields_events`, `wp_amelia_custom_fields_options`, `wp_amelia_custom_fields_services`, `wp_amelia_customer_bookings`, `wp_amelia_customer_bookings_to_events_periods`, `wp_amelia_customer_bookings_to_events_tickets`, `wp_amelia_customer_bookings_to_extras`, `wp_amelia_events`, `wp_amelia_events_periods`, `wp_amelia_events_tags`, `wp_amelia_events_to_providers`, `wp_amelia_events_to_tickets`, `wp_amelia_extras`, `wp_amelia_galleries`, `wp_amelia_locations`, `wp_amelia_locations_views`, `wp_amelia_notifications`, `wp_amelia_notifications_log`, `wp_amelia_notifications_sms_history`, `wp_amelia_notifications_to_entities`, `wp_amelia_packages`, `wp_amelia_packages_customers_to_services`, `wp_amelia_packages_services_to_locations`, `wp_amelia_packages_services_to_providers`, `wp_amelia_packages_to_customers`, `wp_amelia_packages_to_services`, `wp_amelia_payments`, `wp_amelia_providers_to_daysoff`, `wp_amelia_providers_to_google_calendar`, `wp_amelia_providers_to_locations`, `wp_amelia_providers_to_outlook_calendar`, `wp_amelia_providers_to_periods`, `wp_amelia_providers_to_periods_location`, `wp_amelia_providers_to_periods_services`, `wp_amelia_providers_to_services`, `wp_amelia_providers_to_specialdays`, `wp_amelia_providers_to_specialdays_periods`, `wp_amelia_providers_to_specialdays_periods_location`, `wp_amelia_providers_to_specialdays_periods_services`, `wp_amelia_providers_to_timeouts`, `wp_amelia_providers_to_weekdays`, `wp_amelia_providers_views`, `wp_amelia_resources`, `wp_amelia_resources_to_entities`, `wp_amelia_services`, `wp_amelia_services_views`, `wp_amelia_taxes`, `wp_amelia_taxes_to_entities`, `wp_amelia_users`

### 7.2 Amelia Categories

| ID | Name | Status  |
|----|------|---------|
| 1  | Hair | visible |

### 7.3 Amelia Services (9 services)

| ID | Name               | Price | Duration | Category |
|----|--------------------|-------|----------|----------|
| 1  | Стрижка женская    | 100   | 60 min   | Hair     |
| 2  | Стрижка мужская    | 150   | 90 min   | Hair     |
| 3  | Стрижка детская    | 120   | 60 min   | Hair     |
| 4  | Стрижка женская    | 150   | 60 min   | Hair     |
| 5  | Стрижка            | 130   | 60 min   | Hair     |
| 6  | Стрижка 3          | 333   | 60 min   | Hair     |
| 7  | ывсывс (test)      | 23    | 60 min   | Hair     |
| 8  | Sthdbc (test)      | 100   | 60 min   | Hair     |
| 9  | Волосы             | 120   | 60 min   | Hair     |

### 7.4 Amelia Employees (13 providers)

| ID | Name              | Email                    | Status  |
|----|-------------------|--------------------------|---------|
| 1  | Dima First        | wsc8eq@gmail.com         | visible |
| 3  | Маша Иванова      | mashs@ivan.com           | visible |
| 4  | Тина Massy        | tijij@iuehdfijer.com     | visible |
| 5  | Вася Иванов       | dd@dfsdfsd.com           | visible |
| 6  | Anna Travv        | erferf@erfre.com         | visible |
| 7  | qwedwe (test)     | efrf@deferf.com          | visible |
| 8  | Имя2 Фамилия2     | bebos@mybebo.fr          | visible |
| 9  | 99staff Last       | 99diroco@diroco.com      | visible |
| 10 | 00staff last       | 00diroco@diroco.com      | visible |
| 11 | 00ыефаа last       | 00dirocoo@diroco.com     | visible |
| 12 | w100 last          | u7diroco@diroco.com      | visible |
| 13 | s011 last          | 011diroco@diroco.com     | visible |

**Customers:** 1 total

### 7.5 Amelia Locations (37 locations)

| ID | Name           | Address                                                    |
|----|----------------|------------------------------------------------------------|
| 1  | Test Location  | 60 Av. des Champs-Elysees, 75008 Paris, France             |
| 2  | Локация 2      | Esplanade Valery Giscard d'Estaing, 75007 Paris, France    |
| 3  | Saloon2        | Quartier des Champs-Elysees, 8th Arr., Paris               |
| 4  | saloon3        | (no address)                                               |
| 5  | saloon4        | 3rd Arrondissement, Paris                                  |
| 6  | saloon5        | (no address)                                               |
| 7  | saloon6        | (no address)                                               |
| 8  | saloon 7       | (no address)                                               |
| 9–21 | Test entries (23, 232, 444, 4444, etc.) | (no address)                        |
| 22 | saloon44       | (no address)                                               |
| 23 | saloon55       | Val-de-Marne, Ile-de-France, France                        |
| 24 | saloon66       | Hauts-de-Seine, Ile-de-France, France                      |
| 25 | saloon77       | Hauts-de-Seine, Ile-de-France, France                      |
| 26 | saloon88       | Hauts-de-Seine, Ile-de-France, France                      |
| 27 | saloon99       | Hauts-de-Seine, Ile-de-France, France                      |
| 28 | saloon00       | Val-de-Marne, Ile-de-France, France                        |
| 29 | saloon11       | Val-de-Marne, Ile-de-France, France                        |
| 30 | saloon011      | Val-de-Marne, Ile-de-France, France                        |
| 31 | Saloon21       | (no address)                                               |
| 32 | saloon22       | (no address)                                               |
| 33 | r1             | Val-de-Marne, Ile-de-France, France                        |
| 34–37 | s1, s2, s3, s3 | (no address)                                          |

### 7.6 Listeo–Amelia Bridge Mapping (50 records)

Each Listeo listing is linked to an Amelia location via `_amelia_location_id` meta:

| Listing Post ID | Amelia Location ID | Last Sync              |
|-----------------|--------------------|------------------------|
| 496             | 37                 | 2026-02-17 13:45:43    |
| 495             | 36                 | 2026-02-17 13:45:43    |
| 494             | 35                 | 2026-02-17 13:45:43    |
| 493             | 34                 | 2026-02-17 13:45:43    |
| 491             | 33                 | 2026-02-17 13:45:43    |
| 488             | 30                 | 2026-02-17 13:45:42    |
| 485             | 29                 | 2026-02-17 13:45:42    |
| 482             | 28                 | 2026-02-17 13:45:42    |
| 478             | 27                 | 2026-02-17 13:45:42    |
| 475             | 26                 | 2026-02-17 13:45:42    |
| 472             | 25                 | 2026-02-17 13:45:42    |
| 470             | 24                 | 2026-02-17 13:45:42    |
| 467             | 23                 | 2026-02-17 13:45:42    |
| 458             | 22                 | 2026-02-17 13:45:42    |
| 402             | 21                 | 2026-02-17 13:45:42    |
| 401             | 20                 | 2026-02-17 13:45:42    |
| 399             | 19                 | 2026-02-17 13:45:42    |
| 398             | 18                 | 2026-02-17 13:45:42    |
| 397             | 17                 | 2026-02-17 13:45:42    |
| 396             | 16                 | 2026-02-17 13:45:42    |
| 395             | 15                 | 2026-02-17 13:45:42    |
| 394             | 14                 | 2026-02-17 13:45:42    |
| 393             | 13                 | 2026-02-17 13:45:42    |

**All sync timestamps are from 2026-02-17** — Bridge is actively syncing.

---

## 8. WooCommerce Products

| ID  | Name                          | Type             | Status  |
|-----|-------------------------------|------------------|---------|
| 350 | Reverse Withdrawal Payment    | simple           | publish |
| 244–262 | Various listing bookings  | listing_booking  | publish |

Product type `listing_booking` is used by Listeo for paid bookings (Barber Shop, Restaurant, Apartment, etc.).

---

## 9. Key Pages

| ID  | Page                    | Slug               | Purpose              |
|-----|-------------------------|---------------------|----------------------|
| 122 | Rovlex Home             | /rovlex-home        | **Front page**       |
| 457 | Book an Appointment     | /book               | Amelia booking form  |
| 366 | Staff & Services        | /staff-services     | Amelia services page |
| 365 | Master Dashboard        | /master-dashboard   | Employee dashboard   |
| 364 | Salon Dashboard         | /salon-dashboard    | Salon owner panel    |
| 497 | Manager Panel           | /manager-panel      | Admin panel          |
| 158 | Add Listing             | /add-listing        | Listing submission   |
| 161 | My Listings             | /my-listings        | Vendor listings      |
| 162 | Bookings                | /bookings           | Booking management   |
| 154 | Dashboard               | /dashboard          | User dashboard       |
| 14  | My account              | /my-account         | WooCommerce account  |

---

## 10. Listeo REST API Endpoints

| Route                              | Method | Purpose                     |
|------------------------------------|--------|-----------------------------|
| /listeo/v1/listeo-listing-details  | POST   | Get listing structured data |
| /listeo/v1/listeo-hybrid-search    | POST   | Search listings             |
| /listeo/v1/universal-search        | POST   | Universal search            |
| /listeo/v1/rag-chat                | POST   | AI RAG chat                 |
| /listeo/v1/chat-proxy              | POST   | Chat proxy                  |
| /listeo/v1/chat-config             | GET    | Chat configuration          |
| /listeo/v1/contact-form            | POST   | Contact form                |
| /listeo/v1/get-content             | POST   | Get content                 |
| /listeo/v1/woocommerce-product-details | POST | WC product details      |

---

## 11. Regions (44 regions configured)

Top regions: New York (10 listings), Los Angeles (1), plus US states/cities.

---

## 12. Architecture Summary

```
┌─────────────────────────────────────────────┐
│              ROVLEX.COM                      │
│         Beauty/Services Marketplace          │
├──────────┬──────────┬───────────┬────────────┤
│  Listeo  │  Amelia  │  Dokan    │  WooCommerce│
│ Listings │ Booking  │ Vendors   │  Payments   │
├──────────┴──────────┴───────────┴────────────┤
│        ROVLEX Bridge + Integration           │
│   (Sync locations, services, employees)      │
├──────────────────────────────────────────────┤
│  Elementor │ Polylang │ AI Chat │ CF7        │
│  Builder   │  i18n    │ Search  │ Forms      │
├──────────────────────────────────────────────┤
│      WordPress 6.9.1 + Listeo Child Theme    │
├──────────────────────────────────────────────┤
│     PHP 8.1.2 │ MySQL 8.0.45 │ Ubuntu       │
└──────────────────────────────────────────────┘
```

**Flow:**
1. Salon owner registers → Dokan creates vendor store
2. Owner creates Listeo listing (salon profile)
3. ROVLEX Bridge creates Amelia location linked to listing
4. Owner adds services/employees in Amelia
5. Customers book via Amelia on listing page
6. Payment processed via WooCommerce

---

## 13. Notes & Observations

- **Amelia API** (admin-ajax.php) returns 404 for REST calls via Cloudflare — data was extracted via temporary DB export plugin.
- **Bookly plugins** are installed but **inactive** — legacy/evaluation, can be safely removed.
- **Most listings & locations are test data** — IDs 9-21 in Amelia and many listings have garbage names (numbers, random chars). Recommend cleanup before launch.
- **Only 3 WP users, 13 Amelia employees** — platform is in early development/testing phase.
- **All locations are in Paris/Ile-de-France area** — confirms target market is France, aligned with Europe/Paris timezone.
- **Regions taxonomy is US-centric** (New York, LA, etc.) — needs to be replaced with French cities/regions.
- **AI Chat & Search plugin** (v1.8.6) is active — provides RAG-based chat and hybrid search for listings.
- **Bridge sync is working** — all 23+ listings synced on 2026-02-17, 1:1 mapping between Listeo listings and Amelia locations.
- **Services are all in "Hair" category** — need more categories (Nails, Makeup, Skincare etc.) to match Listeo categories.
- **Duplicate services** — "Стрижка женская" appears twice (ID:1 price=100, ID:4 price=150). Needs dedup.
