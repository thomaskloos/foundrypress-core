# FoundryPress Site Refactor Notes

## What was fixed

- Added `hub/bootstrap.php` so Hub pages load a consistent stack:
  - root `includes/config.php`
  - hub `includes/functions.php`
  - hub `includes/auth.php`
- Updated Hub pages to use the shared bootstrap instead of mixing root and hub includes.
- Added missing `hub/setup-password.php` so the existing `.htaccess` route and auth redirects now point to a real file.
- Updated `hub/login.php` to use Hub head/footer assets instead of the public site wrappers.
- Simplified `hub/logout.php` to use the Hub bootstrap.
- Verified PHP syntax across the full site with `php -l`.

## Main structural issue that was causing breakage

The Hub had two parallel include systems:

- `/includes/...` for the public site
- `/hub/includes/...` for the Hub

Several Hub pages were loading a mix of both, which can cause:

- wrong styles/layouts
- missing auth or config behavior
- function redeclaration risks
- paths that work on one page and fail on another

This refactor standardizes Hub entry points while leaving the frontend site structure intact.

## Still worth doing later

- Decide whether `/hub/config.php` should remain the license page or be renamed to `/hub/license.php`.
- Consolidate duplicate helper logic between `/includes/` and `/hub/includes/` once the product structure is finalized.
- Replace hardcoded production URLs in `includes/config.php` with install-time settings if you want portable customer installs.
