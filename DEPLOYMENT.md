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

## 4. Running Migrations Safely on Supabase

> [!IMPORTANT]
> Always run migrations from your **local machine** against the Supabase database URL.  
> Never run `migrate:fresh` or `migrate:reset` on a database with real data.

### 4.1 Setup local `.env` to point to Supabase

Temporarily update your local `.env`:
```env
DB_HOST=db.xxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require
```

### 4.2 Run migrations safely

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

## 5. Seeding Demo Data Safely

Seeders are idempotent — they use `firstOrCreate` to avoid duplicates.

```bash
# Seed categories and equipments only (safe on empty DB)
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=EquipmentSeeder

# Full seed (includes admin user)
php artisan db:seed
```

---

## 6. Creating Admin User Safely

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

## 7. Midtrans Callback URL Configuration

Register these URLs in **Midtrans Dashboard → Settings → Configuration**:

| Setting                  | Value                                        |
|--------------------------|----------------------------------------------|
| Payment Notification URL | `https://YOUR_DOMAIN/midtrans/callback`      |
| Finish Redirect URL      | `https://YOUR_DOMAIN/orders`                 |
| Unfinish Redirect URL    | `https://YOUR_DOMAIN/orders`                 |
| Error Redirect URL       | `https://YOUR_DOMAIN/orders`                 |

> Both `/midtrans/callback` and `/api/midtrans/callback` are registered in `routes/web.php` and excluded from CSRF protection.

---

## 8. Health Check

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

## 9. Manual Smoke Test Checklist

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

## 10. Troubleshooting

| Issue | Fix |
|-------|-----|
| `500 Server Error` after deploy | Check `APP_KEY` is set in Vercel env vars |
| Database connection failed | Verify `DB_SSLMODE=require` and Supabase credentials |
| Midtrans webhook not received | Verify callback URL registered in Midtrans dashboard |
| CSS/JS not loading | Run `npm run build` and ensure `public/build/` is committed |
| Sessions not persisting | Ensure `SESSION_DRIVER=database` and `sessions` table exists |
| Admin 403 error | Ensure user has `role = admin` in users table |
