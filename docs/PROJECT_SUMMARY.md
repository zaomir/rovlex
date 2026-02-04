# 🎯 ROVLEX Project Summary
**Uber-for-Services Marketplace for Beauty & Handyman**

**Version:** 2.0  
**Date:** February 4, 2026  
**Status:** MVP Development

---

## 📌 Project Overview

**ROVLEX** is a two-sided marketplace connecting service professionals (beauty masters, handymen) with customers seeking on-demand services. The platform operates across two vertical markets: **Beauty Services** and **Handyman Services**.

### Core Mission
Become the leading platform for service discovery and booking in the UK & Europe by combining:
- **Customer-facing:** Fast service discovery, transparent pricing, verified professionals
- **Professional-facing:** Steady client flow, professional tools (scheduling, CRM, payments)

---

## 🏗️ Platform Architecture

### Two Vertical Markets

#### 1️⃣ **BEAUTY SERVICES** (Beauty Masters & Salons)
- Hair Services (cuts, coloring, extensions, treatments)
- Nail Services (manicure, pedicure, gel, nail art)
- Makeup Services (bridal, evening, special events)
- Skincare & Facial Services
- Body & Massage Services
- Hair Removal Services
- Tanning & Wellness Services

**Service Model:**
- Masters rent chair/space in salons (pay-per-day / pay-per-hour)
- Get client flow from ROVLEX platform
- Offer services with transparent pricing & availability
- Online booking + calendar management

#### 2️⃣ **HANDYMAN SERVICES** (Local Professionals)
- Plumbing (leak repair, pipe installation, drain cleaning)
- Electrical (light installation, outlets, circuit breaker repair)
- Carpentry (doors, cabinets, shelving, furniture assembly)
- Painting & Decorating
- Flooring Services
- HVAC (heating/cooling installation & repair)
- Appliance Repair
- Glass & Windows
- Locksmith Services
- General Handyman & Repairs
- Landscaping & Outdoor Work
- Cleaning Services
- Pest Control

**Service Model:**
- Independent contractors list their services
- Accept on-demand bookings from platform
- Provide quotes, schedule work, collect payments through ROVLEX
- Build reputation through reviews & ratings

---

## 💰 Business Model

### Revenue Streams

#### **1. Platform Commission**
- Beauty services: **10-25%** per transaction (varies by category/location)
- Handyman services: **15-30%** per booking value
- Higher commission for premium/featured listings

#### **2. Professional Subscriptions**
- **Basic Listing:** Free
- **Premium Profile:** £9.99-19.99/month
  - Priority in search results
  - Detailed analytics & booking insights
  - Professional booking page
  - Client communication tools
  - Review management

#### **3. Featured/Promoted Listings**
- Category-level promotion (homepage featured)
- Location-based advertising (targeted by city/postcode)
- Ad spend: £49-199/month

#### **4. Insurance & Verification**
- Optional professional verification badge (£4.99-9.99/month)
- Background check + insurance verification
- Increases customer trust & booking conversion

---

## 🔧 Technical Stack

### Frontend
- **Homepage:** React/Next.js with Leaflet map
- **Search & Discovery:** Dynamic category filters, location-based
- **Booking:** Custom form integration with Bookly Pro
- **User Dashboard:** Professional profiles, booking history, reviews

### Backend & Data
- **Core Platform:** WordPress Multisite (Listeo theme)
- **Booking System:** Bookly Pro (primary data source)
- **Payment Processing:** Stripe Connect (automated commission collection)
- **Maps:** Leaflet (open-source) + OpenStreetMap
- **Database:** MySQL with custom taxonomy for 100+ service categories
- **API:** REST API for mobile app & third-party integrations

### Infrastructure
- **VDS Hosting:** Ubuntu 22.04 LTS (IP: [REDACTED])
- **CDN:** For image optimization & faster loading
- **Backup:** Daily automated backups

---

## 📊 Service Categories

### **Beauty Services (32 subcategories)**
- Hair Services (9 types)
- Nail Services (9 types)
- Makeup Services (9 types)
- Skincare & Facial (9 types)
- Body & Massage (9 types)
- Hair Removal (9 types)
- Tanning Services (5 types)
- Wellness & Beauty (8 types)

**Total:** 80+ beauty service types

### **Handyman Services (72 subcategories)**
- Plumbing (8 services)
- Electrical (8 services)
- Carpentry (8 services)
- Painting & Decorating (8 services)
- Flooring (7 services)
- HVAC (6 services)
- Appliance Repair (7 services)
- Glass & Windows (7 services)
- Locksmith (5 services)
- General Handyman (10 services)
- Landscaping (7 services)
- Moving & Storage (6 services)
- Cleaning (7 services)
- Pest Control (7 services)

**Total:** 100+ handyman service types

---

## 🎯 Key Features (MVP → Scaling)

### Phase 1: MVP (Months 1-3)
- ✅ Homepage with service categories
- ✅ Leaflet map showing professionals by location
- ✅ Service search & filtering (by category, rating, price)
- ✅ Booking form (integrated with Bookly Pro)
- ✅ Professional profile pages
- ✅ Reviews & ratings system
- ✅ Payment processing (Stripe)
- ✅ Confirmation emails & SMS notifications

### Phase 2: Growth (Months 4-9)
- Mobile app (iOS/Android)
- Advanced filters (availability, price range, insurance verified)
- Loyalty program (points per booking)
- Testimonials & case studies
- Professional chat/messaging
- Flexible pricing (hourly rate vs fixed)
- Package deals for beauty services

### Phase 3: Scale (Months 10-18)
- AI-powered matching (recommend best professional)
- Subscription bundles (beauty packages)
- B2B integrations (corporate wellness, apartment management)
- Affiliate partner network
- Analytics dashboard for professionals

---

## 📈 Growth Targets

### Year 1
- **Cities:** 5-10 major UK cities (London, Manchester, Birmingham, etc.)
- **Professionals:** 1,000-5,000 active listings
- **Customers:** 10,000-50,000 monthly active users
- **Monthly Bookings:** 5,000-15,000
- **Monthly GMV:** £300k-£1M

### Year 2
- **Cities:** Expand to EU (France, Germany, Spain, Netherlands)
- **Professionals:** 10,000+ listings
- **Customers:** 100,000+ monthly active users
- **Monthly Bookings:** 50,000+
- **Monthly GMV:** £5M-£10M

### Key Metrics to Track
- **Conversion Rate:** Homepage visitor → Booking
- **Average Order Value (AOV):** Per booking
- **Customer LTV:** Lifetime value per customer
- **Professional Retention:** Repeat bookings rate
- **Net Promoter Score (NPS):** Customer satisfaction
- **Commission per Service:** Optimize by category

---

## 🗺️ Geographic Expansion Plan

### Phase 1: UK (Priority)
1. **London** (start here - largest market)
2. Manchester
3. Birmingham
4. Leeds
5. Glasgow

### Phase 2: Europe
1. France (Paris, Lyon, Marseille)
2. Germany (Berlin, Munich, Hamburg)
3. Netherlands (Amsterdam, Rotterdam)
4. Spain (Madrid, Barcelona)

### Phase 3: Global
- Replicate model to other English-speaking markets
- Localize payment methods & customer support

---

## 🔐 Trust & Safety

### Professional Verification
- ✅ Identity verification (ID check)
- ✅ Insurance verification (if applicable)
- ✅ Background check option
- ✅ Verified badge on profile
- ✅ Customer reviews & ratings

### Payment Protection
- ✅ Secure Stripe payment processing
- ✅ Funds held until service completion
- ✅ Automatic refund mechanism
- ✅ Dispute resolution system
- ✅ 14-day cancellation guarantee

### Customer Protection
- ✅ Professional ratings & reviews
- ✅ Service guarantee (rebook if unsatisfied)
- ✅ Price transparency (no hidden fees)
- ✅ Secure messaging (no contact exchange on platform)

---

## 💡 Competitive Advantages

1. **Two Verticals in One Platform**
   - Handle both beauty & handyman reduces fragmentation
   - Cross-selling opportunities
   - Network effects as user base grows

2. **Transparent Pricing**
   - Fixed prices upfront (no surge pricing)
   - Deposit options visible at booking
   - No hidden fees

3. **Professional Support**
   - Dedicated support for professionals
   - Tools (scheduling, CRM, invoicing)
   - Marketing support & featured placement

4. **Customer-Centric**
   - Fast booking (minutes, not hours)
   - Quality verified professionals
   - Flexible cancellation & rescheduling

5. **Technology First**
   - AI-powered matching algorithm
   - Real-time availability & scheduling
   - Mobile app with notifications

---

## 📱 Technology Implementation

### Core Integration Points

#### **Bookly Pro → ROVLEX Sync**
- Services & availability pulled from Bookly in real-time
- Bookings created in both ROVLEX + Bookly simultaneously
- Staff calendar automatically updates

#### **Stripe Connect → Commission Automation**
- Service provider receives payment minus commission
- Commission automatically transferred to ROVLEX account
- Weekly settlement reports

#### **REST API → Mobile App**
- All platform features available via API
- Mobile app mirrors web experience
- Push notifications for new bookings

#### **WordPress Multisite → Multi-Domain Strategy**
- rovlex.com (main platform)
- rovlex.uk, rovlex.fr, rovlex.de (country-specific)
- Domain consolidation through internal linking

---

## 📊 Current Status

### Completed ✅
- Category hierarchy (100+ services defined)
- WordPress Multisite setup
- Bookly Pro integration framework
- Form creation (Service + Staff data capture)
- Map component (Leaflet integration)
- Design system (colors, typography, components)

### In Progress 🔄
- Bookly data synchronization
- Payment processing (Stripe Connect setup)
- Homepage template & content
- Professional profiles

### Coming Soon 🚀
- Mobile app (iOS/Android)
- AI matching algorithm
- Advanced analytics dashboard
- Customer loyalty program

---

## 🎨 Brand Identity

### Colors
- **Primary Green:** #02AF08
- **Dark Gray:** #111827
- **Light Gray:** #F3F4F6
- **Accent:** #FFA500 (for important CTAs)

### Typography
- **Headlines:** Poppins (bold, modern)
- **Body:** Inter (clean, readable)

### Messaging
- **Hero:** "Find & Book Local Services in Minutes"
- **For Customers:** Trust, convenience, verified professionals
- **For Professionals:** Steady client flow, professional tools, growth

---

## 📞 Key Contacts & Access

> [!WARNING]
> Credentials have been redacted for security in the repository version.

### Server Access
```
SSH: [REDACTED]
User: [REDACTED]
Password: [REDACTED]

ISPManager: [REDACTED]
User: [REDACTED]
Password: [REDACTED]
```

### WordPress Admin (test.rovlex.com)
```
User: [REDACTED]
Password: [REDACTED]
```

### Database
```
Name: [REDACTED]
User: [REDACTED]
Password: [REDACTED]
```

---

## 📝 Documentation Files

- **ROVLEX_FORM_DOCUMENTATION_v5.md** — Step 2 form (staff & services)
- **WORDPRESS_INTEGRATION_GUIDE_v5.md** — WordPress setup & functions
- **FORM_1_SERVICE_CREATION.md** — Service creation API
- **rovlex_categories_hierarchy.md** — Complete category taxonomy
- **TESTING_TASK_BOOKLY_ROVLEX_INTEGRATION.md** — Integration testing guide

---

## 🎯 Next Steps for Development

### Immediate (This Week)
1. Finalize homepage design & content
2. Set up payment processing (Stripe Connect testing)
3. Complete Bookly Pro configuration for test location
4. Begin booking flow testing

### Short-term (This Month)
1. Launch MVP with 2-3 test cities
2. Onboard 50-100 professionals (beta)
3. Process first 100 bookings
4. Iterate based on user feedback

### Mid-term (Months 2-3)
1. Expand to 5 UK cities
2. Onboard 1,000+ professionals
3. Hit 5,000+ monthly bookings
4. Begin content marketing strategy

---

## ✨ Success Metrics

- **Monthly Bookings:** Target 5,000+ by Month 3, 50,000+ by Year 2
- **Customer Retention:** 40%+ monthly return rate
- **Professional Retention:** 60%+ continue listing after 3 months
- **Average Order Value:** £75+ (beauty average £50-100, handyman £80-150)
- **NPS Score:** 50+ (excellent)
- **Platform Uptime:** 99.9%+

---

**Project Owner:** Total  
**Last Updated:** February 4, 2026  
**Status:** Active Development 🚀
