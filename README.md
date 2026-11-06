# Kim Ralfonzo Diwa: PHP portfolio

Public site (`index.php`) plus a private admin at `/admin/` for projects and reviews. Needs PHP 8.0+ (no database, no Composer).

## Run it locally

PHP is not installed on this computer yet. Install it (for example `winget install PHP.PHP.8.3`), then:

```
php -S localhost:8000
```

Open http://localhost:8000 for the site and http://localhost:8000/admin/ for the admin.
On first visit to the admin you choose your own password (10+ characters). It is stored hashed in `data/config.json`.

## Where things live

- `index.php`: the public page. Edit the skills, experience and email directly in this file.
- `assets/style.css`, `assets/app.js`: look, loading intro, scroll reveals.
- `admin/index.php`: login and the Projects and Reviews editors.
- `inc/lib.php`: helpers (JSON storage, login, CSRF, image upload).
- `data/content.json`: projects and reviews. `uploads/`: images added from the admin.

## Hosting

Any PHP host (shared hosting, a VPS) works. Upload the whole folder.

- Make `data/` and `uploads/` writable by PHP.
- Apache reads the included `.htaccess` files to block direct access to `data/` and `inc/`. On nginx, add rules denying `/data/` and `/inc/`, or move `data/` outside the web root and change `DATA_DIR` in `inc/lib.php`.
- Serve over HTTPS so the admin login cookie is secure.

## Not ported from the Node version

The chat widget and visitor analytics. Say the word if you want those in PHP too.
