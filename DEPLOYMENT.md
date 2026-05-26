# Manake V2 — Deployment Guide
## Supabase PostgreSQL + Vercel

> **Scope:** Manake V2 — Sistem Manajemen Penyewaan Alat Media Manake Berbasis Payment Gateway  
> **Stack:** Laravel 12 · Blade · Tailwind/Vite · PostgreSQL Supabase · Midtrans Snap · DomPDF

---

## ⚠️ Critical Safety Rules

> [!CAUTION]
> **NEVER run `php artisan migrate:fresh` or `php artisan db:wipe` on Supabase production.**  
> This will permanently destroy all rental data, orders, and user accounts.

> [!CAUTION]
> **NEVER commit `.env` to Git.**  
> All secrets must be configured as Vercel Environment Variables only.

> [!WARNING]
> **Vercel is serverless — no persistent storage.** Do NOT use local disk uploads.  
> Log channels must use `stack`/`single`, not `daily` (no persistent filesystem).

---

## 1. Supabase PostgreSQL Setup

### 1.1 Create a Supabase Project
1. Go to [https://supabase.com](https://supabase.com) → New Project
2. Choose a region closest to your users (e.g., Southeast Asia)
3. Set a strong database password

### 1.2 Get Database Credentials
Go to: **Project → Settings → Database → Connection Info**

Use the **Session mode** connection (port **5432**) for Laravel:

| Variable      | Where to find it                              |
|---------------|-----------------------------------------------|
| `DB_HOST`     | Host field (e.g. `db.xxxx.supabase.co`)       |
| `DB_PORT`     | `5432`                                        |
| `DB_DATABASE` | `postgres`                                    |
| `DB_USERNAME` | `postgres`                                    |
| `DB_PASSWORD` | The password you set when creating the project |

> [!IMPORTANT]
> Always use `DB_SSLMODE=require` when connecting to Supabase from production.

---

## 2. Generate APP_KEY Locally

Run this **locally** (not on Vercel):

```bash
php artisan key:generate --show
```

Copy the output (starts with `base64:...`) and set it as the `APP_KEY` environment variable in Vercel.

---

## 3. Vercel Deployment

### 3.1 Required Environment Variables

Set all of these in **Vercel Dashboard → Project → Settings → Environment Variables**:

```
APP_NAME="Manake V2"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-vercel-domain.vercel.app

DB_CONNECTION=pgsql
DB_HOST=db.xxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
DB_SCHEMA=manake_v2

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MIDTRANS_MERCHANT_ID=your_merchant_id
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

ADMIN_SEED_EMAIL=admin@manake.id
ADMIN_SEED_PASSWORD=your_secure_admin_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@manake.id
MAIL_FROM_NAME="Manake"
```

### 3.2 Vercel Build Settings

The `vercel.json` already configures:
- **Build Command:** `composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache`
- **Output Directory:** `public`
- **PHP Runtime:** `vercel-php@0.7.2` via `api/index.php`

### 3.3 Deploy

```bash
# Install Vercel CLI (if not installed)
npm i -g vercel

# Link and deploy
vercel --prod
```

Or connect your GitHub repo to Vercel for automatic deploys on push.

---

## 4. Schema Isolation — Create `manake_v2` Schema

> [!IMPORTANT]
> The existing Supabase project already has old Manake tables in the `public` schema  
> (`users`, `categories`, `equipments`, `orders`, etc.).  
> Manake V2 MUST use a **separate schema** to avoid collision.

### 4.1 Create the isolated schema in Supabase

Go to: **Supabase Dashboard → SQL Editor** and run:

```sql
-- Create the dedicated schema for Manake V2
CREATE SCHEMA IF NOT EXISTS manake_v2;

-- Grant usage to your postgres user
GRANT USAGE ON SCHEMA manake_v2 TO postgres;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA manake_v2 TO postgres;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA manake_v2 TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA manake_v2 GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA manake_v2 GRANT ALL ON SEQUENCES TO postgres;
```

This SQL is **safe** — it does NOT touch or drop any existing `public` schema tables.

### 4.2 What this achieves

| Schema | Contents | Status |
|--------|----------|--------|
| `public` | Old Manake V1 tables | ✅ Preserved — not touched |
| `manake_v2` | All Manake V2 tables | ✅ Clean — safe to migrate |

> [!CAUTION]
> **NEVER import old data by running migrations into `public`.**  
> If you want to migrate old records to V2, do it with explicit `INSERT INTO manake_v2.table SELECT ... FROM public.table` — never via destructive migration commands.

---

## 5. Running Migrations Safely on Supabase

> [!IMPORTANT]
> Always run migrations from your **local machine** against the Supabase database URL.  
> Never run `migrate:fresh` or `migrate:reset` on a database with real data.

### 5.1 Setup local `.env` to point to Supabase

Temporarily update your local `.env`:
```env
DB_HOST=db.xxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
DB_SCHEMA=manake_v2
```

> [!IMPORTANT]
> `DB_SCHEMA=manake_v2` tells Laravel to run all queries and migrations inside the `manake_v2` schema.  
> The old `public` schema data is completely untouched.

### 5.2 Run migrations safely

```bash
# Check which migrations have not been run yet
php artisan migrate:status

# Run only pending migrations (safe — does NOT drop tables)
php artisan migrate --force

# Rollback only the LAST batch (safe — only if needed)
php artisan migrate:rollback
```

> [!CAUTION]
> `php artisan migrate:fresh` — **DROPS ALL TABLES AND RECREATES THEM.**  
> `php artisan db:wipe` — **DELETES EVERY TABLE IN THE DATABASE.**  
> **NEVER use these on Supabase production.**

---

## 6. Seeding Demo Data Safely

Seeders are idempotent — they use `firstOrCreate` to avoid duplicates.

```bash
# Seed categories and equipments only (safe on empty DB)
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=EquipmentSeeder

# Full seed (includes admin user)
php artisan db:seed
```

---

## 7. Creating Admin User Safely

### Option A — Via Seeder (recommended)
Set `ADMIN_SEED_EMAIL` and `ADMIN_SEED_PASSWORD` in `.env`, then:
```bash
php artisan db:seed --class=DatabaseSeeder
```

### Option B — Via Artisan Tinker
```bash
php artisan tinker

# Inside tinker:
\App\Models\User::create([
    'name'     => 'Admin Manake',
    'email'    => 'admin@manake.id',
    'password' => bcrypt('your_secure_password'),
    'role'     => 'admin',
]);
```

### Option C — Promote existing user
```bash
php artisan tinker

# Inside tinker:
\App\Models\User::where('email', 'your@email.com')->update(['role' => 'admin']);
```

---

## 8. Midtrans Callback URL Configuration

Register these URLs in **Midtrans Dashboard → Settings → Configuration**:

| Setting                  | Value                                        |
|--------------------------|----------------------------------------------|
| Payment Notification URL | `https://YOUR_DOMAIN/midtrans/callback`      |
| Finish Redirect URL      | `https://YOUR_DOMAIN/orders`                 |
| Unfinish Redirect URL    | `https://YOUR_DOMAIN/orders`                 |
| Error Redirect URL       | `https://YOUR_DOMAIN/orders`                 |

> Both `/midtrans/callback` and `/api/midtrans/callback` are registered in `routes/web.php` and excluded from CSRF protection.

---

## 9. Health Check

Before going live, run:

```bash
php artisan manake:health
```

Expected output (all PASS):
```
╔══════════════════════════════════════════════╗
║      Manake V2 — Deployment Health Check     ║
╚══════════════════════════════════════════════╝

  ✓ PASS  APP_KEY is set
  ✓ PASS  APP_URL is set
  ✓ PASS  APP_DEBUG is false (production safe)
  ✓ PASS  Database connection works
  ✓ PASS  Table [users] exists
  ...
  ✓ PASS  MIDTRANS_SERVER_KEY is set
  ✓ PASS  MIDTRANS_CLIENT_KEY is set

  ✓ All critical checks passed. Safe to deploy.
```

---

## 10. Manual Smoke Test Checklist

After deploying, verify these manually:

- [ ] Homepage loads without errors (`/`)
- [ ] Catalog page shows equipment (`/catalog`)
- [ ] Equipment detail page loads (`/product/{slug}`)
- [ ] Register a new user account
- [ ] Login works
- [ ] Add item to cart
- [ ] Checkout preview shows correct total + tax
- [ ] Midtrans Snap popup appears on checkout
- [ ] Complete sandbox payment in Midtrans test
- [ ] Webhook received — order status updates to `paid`
- [ ] Invoice PDF downloadable (`/orders/{id}/invoice/download`)
- [ ] Admin login works (`/admin/dashboard`)
- [ ] Admin can CRUD categories and equipments
- [ ] Admin can view and update order status
- [ ] Admin can add late/damage fees

---

## 11. Troubleshooting

| Issue | Fix |
|-------|-----|
| `500 Server Error` after deploy | Check `APP_KEY` is set in Vercel env vars |
| Database connection failed | Verify `DB_SSLMODE=require` and Supabase credentials |
| Tables not found after migration | Verify `DB_SCHEMA=manake_v2` is set AND schema was created with SQL above |
| Migrations ran into wrong schema | Check `DB_SCHEMA` env var — must be `manake_v2` not `public` |
| Midtrans webhook not received | Verify callback URL registered in Midtrans dashboard |
| CSS/JS not loading | Run `npm run build` and ensure `public/build/` is committed |
| Sessions not persisting | Ensure `SESSION_DRIVER=database` and `sessions` table exists |
| Admin 403 error | Ensure user has `role = admin` in users table |
