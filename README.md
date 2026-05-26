# Manake Media Rental Management System (V2)

An elegant, modern implementation of the Manake Media Equipment Rental Management website, strictly aligned with the academic thesis proposal:
**“Sistem Manajemen Penyewaan Alat Media Manake Berbasis Payment Gateway dengan Optimalisasi Proses Bisnis.”**

---

## 📋 Academic Context & Business Process Optimization

This project serves as the primary implementation artifact for a computer science academic proposal focusing on optimizing traditional media rental operations. Traditional rental operations face bottlenecks in manual scheduling, double-booking errors, delayed security deposit refunds, and cash-in-hand friction.

The **Manake V2** solution optimizes these processes via:
1. **Real-time Availability Checks**: Preventing double-booking with transactional inventory status locks.
2. **Automated Rental Lifecycle**: Standardized workflow transitions from pending checkout, active handovers, return condition checks, late return penalty fee calculations, to final resolution.
3. **Integrated Payment Gateway**: Streamlining payments and deposits through Midtrans Snap Sandbox, automating order validation via secure webhook notifications.
4. **Digital Auditing & Invoicing**: Instant automated PDF invoice generation via Barryvdh DomPDF to guarantee transparent financial tracing.

---

## 🛠️ Technology Stack

- **Framework**: Laravel 12 (Core MVC architecture)
- **Language**: PHP 8.3+
- **Frontend Engine**: Laravel Blade (Structured template views)
- **Styling**: Tailwind CSS (integrated via Vite asset pipeline)
- **Dynamic Interaction**: Alpine.js (Lightweight interactive elements)
- **Database**: PostgreSQL hosted on Supabase (No destructive migrations to respect existing structures)
- **Payment Gateway**: Midtrans Snap API Sandbox
- **Reporting Engine**: Barryvdh Laravel DomPDF (Dynamic PDF generation)

---

## 📂 Architecture & Core Services

To align with modern separation of concerns and avoid bloated controllers, business logic is isolated in modular domain service classes located in `app/Services/`:

1. **`AvailabilityService`**:
   - Manages stock counts, overlapping date ranges, and optimistic/pessimistic lock mechanisms during checkouts.
2. **`CartService`**:
   - Manages user's temporary rental cart in the database or session, enforcing quantity bounds.
3. **`CheckoutService`**:
   - Orchestrates calculations (rental fee * days, security deposits, discounts, total charges) and initiates transaction tokens.
4. **`MidtransService`**:
   - Handles sandbox Snap integration, generates payment tokens, and processes secure server webhook signals.
5. **`InvoiceService`**:
   - Generates official customer invoices in PDF format using HTML/CSS view templates compiled via DomPDF.
6. **`OrderStatusService`**:
   - Controls state transitions of the rental order (e.g., `Pending` ➡️ `Paid` ➡️ `Picked Up / Active` ➡️ `Returned` ➡️ `Completed` or `Late Penalty`).

---

## ⚡ Local Installation Guide

### Prerequisites
Make sure you have the following installed on your developer machine:
- PHP 8.3 or higher (PHP 8.5+ recommended)
- Composer 2.0+
- Node.js & NPM (latest LTS)
- PostgreSQL (if hosting database locally instead of Supabase)

### Step 1: Clone the Repository
```bash
git clone https://github.com/frahmat68-beep/Manake-V2.git
cd "Manake V2"
```

### Step 2: Install Composer & NPM Dependencies
```bash
composer install
npm install
```

### Step 3: Set Up Environment Configuration
Duplicate the example environment file:
```bash
cp .env.example .env
```
Open `.env` in your editor and supply your credentials:
- **Supabase Database connection**: Fill in `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and ensure `DB_CONNECTION=pgsql`.
- **Midtrans Credentials**: Fill in your sandbox client/server key.

Generate a secure application key:
```bash
php artisan key:generate
```

### Step 4: Run Database Migrations
Run standard, non-destructive migrations to initialize Breeze and baseline tables:
```bash
php artisan migrate
```
> [!WARNING]
> DO NOT run `migrate:fresh` or `db:wipe` if connecting directly to a production database.

### Step 5: Compile Assets & Start the Development Servers
Compile Vite assets and boot up the local servers:
```bash
npm run build
```
Or start local hot-reloads:
```bash
npm run dev
```

Run Laravel's built-in web server:
```bash
php artisan serve
```

Access the application in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Verification & Quality Control

### Run Automated Tests
```bash
php artisan test
```

### Manual Visual Auditing
Access `/register` and `/login` to verify the Tailwind CSS + Alpine.js powered Blade templates render properly.
