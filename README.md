# FinTrack — Personal Budget Tracker (Laravel)

A complete, production-quality budget tracker: income & expense management, category budgets
with progress alerts, a live dashboard with charts, and multi-sheet **Excel export** reports.

## ✨ Features

- **Auth** — register/login with per-user data isolation and currency preference (USD, EUR, GBP, KES, NGN, ZAR, INR, JPY)
- **Transactions** — add/edit/delete income & expense entries with category, payment method, notes, recurring flag; search, filter by type/category/date range, sortable + paginated table
- **Categories** — 19 sensible defaults (Housing, Groceries, Salary, Freelance, etc.) plus unlimited custom categories with icon + color
- **Budgets** — set a monthly spending limit per category, with a live progress bar (green → amber → red) on both the dashboard and the Budgets page
- **Dashboard** — income/expense/balance/savings-rate stat cards with month-over-month change, a 6-month income vs. expense bar chart, a spending-by-category donut chart, recent transactions, and budget progress
- **Reports** — custom date-range summary, 12-month trend line chart, category breakdown, and a **4-sheet Excel workbook export** (Summary, Transactions, By Category, Monthly Trend) via `maatwebsite/excel`
- **Polish** — dark mode, responsive/mobile sidebar, Lucide icons, Chart.js visuals, Alpine.js interactivity — no JS build step required (Tailwind/Alpine/Chart.js all load from CDN)

## 🗂 What's in this package

This is the **application layer** (models, migrations, controllers, views, routes, exports) for a
Laravel 11 project. To keep things safe and version-accurate, you'll drop these files into a fresh
Laravel skeleton pulled directly from Packagist, rather than relying on a hand-built framework core.

```
app/
  Models/            User, Category, Transaction, Budget
  Http/Controllers/  Auth, Dashboard, Transaction, Category, Budget, Report
  Exports/           4 export classes powering the Excel report
database/
  migrations/        4 migrations (users profile fields, categories, transactions, budgets)
  seeders/           19 default categories
resources/views/     All Blade templates (layout, auth, dashboard, transactions, categories, budgets, reports)
routes/web.php       All application routes
composer.json        Dependency list (Laravel 11 + maatwebsite/excel)
.env.example          Environment template (SQLite by default)
```

## 🚀 Setup (5 minutes)

```bash
# 1. Create a fresh Laravel 11 project
composer create-project laravel/laravel budget-tracker
cd budget-tracker

# 2. Copy this package's files into it (overwrite when prompted)
#    — unzip fintrack-budget-tracker.zip somewhere, then:
cp -R ../fintrack-budget-tracker/app/* app/
cp -R ../fintrack-budget-tracker/database/* database/
cp -R ../fintrack-budget-tracker/resources/* resources/
cp ../fintrack-budget-tracker/routes/web.php routes/web.php
cp ../fintrack-budget-tracker/.env.example .env.example

# 3. Install the Excel package
composer require maatwebsite/excel

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# SQLite is the fastest way to get started:
touch database/database.sqlite
# (If you'd rather use MySQL, edit .env — the commented block shows the DB_* variables)

# 5. Migrate & seed default categories
php artisan migrate --seed

# 6. Run it
php artisan serve
```

Visit **http://localhost:8000**, register an account, and start tracking. Since Tailwind, Alpine.js,
Chart.js and Lucide icons all load from CDN, there's no `npm install`/`npm run build` step required.

## 📊 Using the Excel export

Go to **Reports → pick a date range → Export to Excel**. You'll get a `.xlsx` file with 4 sheets:

1. **Summary** — total income, expense, net savings, savings rate for the period
2. **Transactions** — every transaction in range (date, type, category, description, method, amount, notes)
3. **By Category** — expense totals, transaction counts, and % share per category
4. **Monthly Trend** — income/expense/net for the trailing 12 months

## 🔒 Notes on security & data isolation

Every controller scopes queries to `auth()->user()->id`, and ownership is explicitly checked before
update/delete on transactions, categories, and budgets (`abort_if($resource->user_id !== ...)`).
Default (system) categories have `user_id = null` and are shared/read-only across all users.

## 🛠 Extending it further

Ideas for a v2, if you want to keep building:
- CSV/receipt import
- Multi-currency conversion (store an exchange rate snapshot per transaction)
- Recurring transactions auto-posting via a scheduled `artisan` command
- PDF report export (`barryvdh/laravel-dompdf`) alongside the Excel one
- Shared/family budgets with multiple users per "household"
