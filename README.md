# ChaoChan Fried Rice — Offline Restaurant POS (Laravel Core Build)

**Build status: Core-source starter / pilot build.** This is real Laravel source code for cashier POS, table QR ordering, pay-first / pay-later, kitchen board, payment recording and starter admin functions. **Not yet certified production-ready.** The Laravel dependencies (`vendor/`) are **not** in this ZIP: install them using Composer once during setup. The authoring container could not reach Composer/Packagist or run a MySQL-backed Laravel application, so full end-to-end execution has **not** been verified. PHP files and JavaScript have been syntax-checked.

**No Bootstrap / No Tailwind / No Firebase / No CDN.** The interface uses original local CSS and vanilla JS. All normal ordering operations run on your restaurant's local LAN after one-time installation.

## Included pages

- Staff login and role routing (Admin / Manager / Cashier / Kitchen)
- Dashboard with collections, pending QR orders, kitchen orders and bestseller summary
- Cashier POS (26 starter menu items; dine-in/take-out; two payment modes; editable cart)
- Public QR menu and live order-status tracking without customer login
- Live Orders queue: confirm QR orders, collect verified payments, view kitchen status
- Kitchen Display: New → Preparing → Ready → Served (4-second polling)
- Printer-friendly 80mm bill, with unpaid/paid state and recorded payments
- Table list: create tables, generate local QR SVGs, close settled/served tables
- Menu Management: add/edit price, photo, availability
- Cashier Shift: open, review drawer estimate, close shift
- Payment ledger with date filters and CSV export
- Expense records and manual ingredient inventory
- Administrator staff management with roles and activation

**Example initial data:** All 26 items from ChaoChan's uploaded menu and eight sample tables (01–08). Replace or add tables to match the real restaurant layout.

## Requirements (Windows / Laragon)

- Windows computer acting as the server, with static or DHCP-reserved LAN IP.
- Laragon (Apache or Nginx + PHP **8.2+** with needed PHP extensions + MySQL 8 / compatible MariaDB).
- Composer installed on the server and internet **once** to download PHP dependencies. Alternatively use an approved internally mirrored package repository.
- PHP extensions commonly required by Laravel/QR: `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `xml`, `ctype`, `tokenizer`, etc. Verify with `php -m`.
- Other cashier devices, kitchen screens and customer smartphones use a browser on the same allowed local network.

## Installation — local Windows Laragon

1. Extract into `C:\\laragon\\www\\chaochan-pos` (the extracted folder must directly contain `artisan`, `composer.json`, `public`, etc.).
2. Launch Laragon, start **Apache/Nginx** and **MySQL**. Open Laragon's Terminal in `C:\\laragon\\www\\chaochan-pos`.
3. Run `composer install` (requires a network or package cache the first time).
4. Copy `.env.example` to `.env` (`copy .env.example .env`).
5. Create the empty MySQL database `chaochan_pos_db` (via HeidiSQL/phpMyAdmin/SQL).
6. Edit `.env`: DB host/name/user/password, APP_URL with your actual local IP (example `http://192.168.1.100`), APP_TIMEZONE=Asia/Manila.
7. Run `php artisan key:generate` and `php artisan migrate --seed`.
8. Run `php artisan chaochan:admin` and enter a **unique password at least 12 characters long**. No default administrator credentials are shipped.
9. Run `php artisan storage:link` and `php artisan optimize:clear`.
10. Configure Laragon Apache/Nginx document root to the project's `public` folder and serve it through your reserved LAN IP. Use the web server **not** `php artisan serve` for sustained restaurant use.
11. Open the app from a cashier computer, log in with the administrator account, add cashier and kitchen accounts, then have cashiers open shifts before taking payments.
12. Go to **Tables & QR**, confirm APP_URL points to the reachable LAN host (NOT localhost), print QR codes, connect a test phone to guest Wi-Fi and place a test order.
13. Disconnect the internet **but leave LAN/Wi-Fi working**. Verify QR → Cashier → Kitchen → Payment → Table Close.

### Basic MySQL database creation

```sql
CREATE DATABASE chaochan_pos_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Why the LAN IP matters

Use `APP_URL=http://192.168.1.100` only as an **example**. Replace it with the PC's actual reserved LAN IP/hostname. If you visit `http://localhost` on the main PC, that address is **not** reachable by phones. QR codes use APP_URL to prevent embedding localhost by mistake.

Use secure guest Wi-Fi segmentation / firewall so guests can reach the ordering web service but not MySQL, admin tools, or other internal devices. Plain HTTP should not be exposed to the public internet. Browser features requiring a secure context may need LAN HTTPS.

## Local automated tests after Composer installation

To run the included Laravel Feature tests (requires PHP SQLite extension):

```bat
php artisan test
```

Tests cover pay-first kitchen gating, QR pay-later confirmation, and server-side price authority. They have **not** been executed in the build container; add concurrency/integration tests before production use.

## Demo/test workflow

1. Sign in as administrator; add an employee with role `cashier` and one with role `kitchen`.
2. Cashier → **My Cashier Shift** → open a shift with e.g. ₱1,000 opening cash.
3. On Table 01's QR link, customer adds H1 + Extra Egg and chooses **Pay Later**.
4. Cashier → Live Orders → **Confirm**; Kitchen Display shows the order within ~4 seconds.
5. Kitchen → Start Cooking → Mark Ready → Mark Served.
6. Cashier → Live Orders → **Collect Payment** (Bill total ₱135). Cash amount tendered ₱200 gives ₱65 change; enter amount to apply ₱135 and tendered ₱200.
7. Tables & QR → close Table 01; visit should close because payment and kitchen fulfillment are complete.
8. Repeat with Pay First; kitchen must remain empty for that order until accepted and fully paid.
9. Repeat the same order on two cashier browsers and verify transaction isolation; repeat the same payment request to test idempotency.

## Architecture / core controls

- Laravel server recalculates all prices from MySQL; browser totals are only previews.
- MySQL transactions and row locks protect bill settlement and kitchen actions.
- Each order and payment has a unique idempotency key.
- Dine-in tables lock during visit creation so simultaneous customer requests join the same open visit.
- Guest QR orders always await cashier confirmation; pay-first orders additionally require verified payment before cooking.
- GCash **manual verification** is required by the cashier; a receipt image/reference alone does not confirm settlement.
- All kitchen actions and payments write audit records.
- Admin credentials are **not** preset; password entered interactively.
- Cashier payments require an open shift.
- Public menu and assets are served locally; no cloud calls or CDNs.

## Important gaps before true production launch

This ZIP is a **functional starter implementation**, not a complete commercial checkout product. Please complete and test the following before collecting real money:

1. **Run end-to-end Laravel/MySQL tests on your Windows/Laragon environment.** The generating container had no Composer/network/DB drivers, so app runtime was not executed here.
2. Add robust **end-to-end concurrency and receipt/payment integration tests** under realistic load; validate USB thermal printers (58/80 mm), exact receipt formats, and reconnect behavior.
3. Current live kitchen/cashier updates use **4–5-second AJAX polling**, not Laravel Reverb. Local Reverb broadcasting, notification sounds, kitchen screensaver/wake handling and background service recovery are future upgrades.
4. PDF/Excel reports, direct thermal printer driver integration, payment refunds/voids, discounts, split payment, transaction rollback workflows, and full detailed finance reporting are not implemented yet. Basic CSV export is included.
5. Manual inventory adjustment works; **recipe-based automatic deductions**, supplier purchases and ingredient costing are not yet implemented.
6. Set up **encrypted daily backups**, off-PC copy, tested restoration, UPS, user auditing/reporting, logging/alerting, and server startup supervision.
7. Review BIR invoice compliance separately; sample transaction views are **not official invoices**.
8. Hardening: local HTTPS, stricter guest Wi-Fi access, session/permission checks, login testing, owner PIN approval for exceptional actions, security review and accessibility QA.
9. Consider paginated API endpoints and server-side filtering before high-volume use; today's order board limits to 120 and CSV to 1,000 payments.
10. There is no existing-data migration. Back up any old system and do not overwrite a live database.

## Quick troubleshooting

- **`vendor/autoload.php` missing:** run `composer install` in the project root.
- **`SQLSTATE[HY000]`:** create the database and check credentials in `.env`.
- **`No application encryption key`:** run `php artisan key:generate`.
- **Images not visible:** run `php artisan storage:link`, check local public storage permissions.
- **QR works on server but not mobile:** make sure APP_URL is the reachable LAN IP, phone on allowed Wi-Fi, correct firewall access to the web server and guest AP isolation is configured appropriately.
- **Kitchen does not update:** verify Kitchen role access and that `/kitchen/feed` responds; auto-refresh uses JS and does not require a WebSocket service.
- **Pay button errors:** open a Cashier Shift first and verify GCash details when necessary.
- **APP_DEBUG:** must be `false` in production; never expose stack traces to customers.

## Notes for contributors

Core endpoints are in `routes/web.php`. The central order write/locking logic is in `app/Services/OrderService.php`; the database schema is under `database/migrations`; menu/table seed data is `database/seeders/DatabaseSeeder.php`. UI files: `resources/views` + `public/css/app.css` + `public/js`.
