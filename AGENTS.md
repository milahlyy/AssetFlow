# AGENTS.md

Guidance for coding agents working in this repository.

## Project Overview

AssetFlow is a plain PHP/MySQL web app for managing office asset loans. It supports these roles:

- `pegawai`: browse available assets, submit loan requests, view personal loan status/history, return assets.
- `hrga`: manage users/assets, approve or reject loan requests, view reports.
- `satpam`: update vehicle exit/entry timestamps.
- `supir`: update vehicle mileage and condition notes for assigned trips.

The app is intentionally simple: PHP files live at the repository root, CSS is in `css/`, static images are in `assets/img/`, and the SQL schema/seed data is in `database/assetflow.sql`.

## Local Setup

Typical local stack is XAMPP/MAMP-style PHP + MySQL.

1. Create/import the `assetflow` database from `database/assetflow.sql`.
2. Configure DB credentials in `database/db.php`.
3. Serve the repository as a PHP web root.
4. Login with the seeded accounts from `database/assetflow.sql`.

There is currently no Composer setup, package manager, or automated test suite.

## Useful Checks

Run PHP syntax checks before finishing PHP changes:

```powershell
Get-ChildItem -LiteralPath 'D:\[0]_lifeline\miaomiao\github\AssetFlow' -Filter '*.php' -File | ForEach-Object { php -l $_.FullName }
```

If editing SQL, inspect `database/assetflow.sql` manually and avoid non-standard whitespace/mojibake characters.

## Code Structure

- `database/db.php`: creates `$conn` as a PDO connection and starts the session.
- `auth_check.php`: requires login, enforces idle timeout, and defines `checkrole($allowed_roles)`.
- `login.php` / `logout.php`: authentication entry/exit.
- `index.php`, `katalog_aset.php`, `form_peminjaman.php`, `riwayat_saya.php`: pegawai flow.
- `admin_dashboard.php`, `kelola_aset.php`, `kelola_user.php`, `persetujuan.php`, `laporan.php`: HRGA flow.
- `dashboard_operasional.php`, `galeri_mobil.php`, `detail_mobil.php`, `form_satpam.php`, `form_supir.php`, `riwayat.php`: operational flow.

## Implementation Rules

- Keep the app plain PHP unless the user explicitly asks for a framework or dependency.
- Use PDO prepared statements for all queries with user input.
- Call `require_once 'database/db.php';` and `require_once 'auth_check.php';` on protected pages, then call `checkrole([...])`.
- Escape all user/database output with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`, including textareas, attributes, filenames, names, division values, rejection reasons, and notes.
- Do not trust hidden form inputs. Re-check ownership/role constraints in the `UPDATE` or `DELETE` query itself.
- Prefer POST for state-changing actions. Avoid destructive actions through GET links.
- Add CSRF tokens when adding or touching forms that create, update, delete, approve, reject, or return records.
- Validate enum-like inputs in PHP before writing to DB, even if the database has enum columns.
- For uploads, validate file size, extension, and MIME type; generate a safe server filename; never trust the original filename directly.
- Keep redirects followed by `exit();`.

## Security Hotspots

Be extra careful in these areas:

- `kelola_aset.php`: image upload and asset deletion.
- `kelola_user.php` / `user_edit.php`: user creation, editing, deletion, role changes.
- `persetujuan.php`: approval/rejection should only affect `pending` loans.
- `form_satpam.php`: satpam updates should only affect valid vehicle loans and allowed statuses.
- `form_supir.php`: supir updates must be scoped to the current `driver_id`.
- `index.php`: returning assets should remain scoped to the current pegawai.

## UI/CSS Notes

Existing pages use role-specific CSS files rather than a shared layout system. Keep visual changes consistent with the current sidebar/dashboard style unless the user asks for a redesign.

## Before Finishing

- Run `php -l` on changed PHP files, or all root PHP files for broader changes.
- Check that role redirects still point to existing pages.
- Check that new DB fields are reflected in `database/assetflow.sql`.
- Mention any checks you could not run.
