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

## 7. Listeo–Amelia Integration (Key Finding)

**Listing "saloon44" (ID:458) meta:**

```
Amelia Location Id:           22
Custom Tab Amelia Location Id: 22
Booking Enabled:              1
Booking Opens Apply:          immediately
Booking Closes Apply:         never
Rovlex Last Sync:             2026-02-17 13:34:27
```

The **ROVLEX Amelia Bridge v1.0.1** plugin synchronizes data between Listeo listings and Amelia locations:
- Each listing gets an `amelia_location_id` meta field
- Sync timestamp is tracked via `rovlex_last_sync`
- Booking settings (opens/closes) are managed per listing

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

- **Amelia API** returns empty responses publicly (likely requires WP nonce for authenticated calls). Data access confirmed only through Listeo bridge metadata.
- **Bookly plugins** are installed but **inactive** — appears to be legacy or being evaluated as alternative to Amelia.
- **Most listings are test data** — only "saloon" entries are real. Recommend cleanup before launch.
- **Only 3 users** in the system — platform is in early development/testing phase.
- **Regions** are US-centric but timezone is Europe/Paris — may need alignment.
- **AI Chat & Search plugin** (v1.8.6) is active — provides RAG-based chat and hybrid search for listings.
