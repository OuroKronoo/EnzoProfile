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

## Deploy on Render (Docker)

The repo includes a `Dockerfile` (PHP 8.3 + Apache) and a `render.yaml` blueprint.

1. Push the repo to GitHub.
2. In Render, choose New > Blueprint and select the repo (or create a Web Service with Environment set to Docker).
3. Add a persistent disk mounted at `/var/data`. The blueprint already does this.
4. Open `/admin/` and choose your password.

Notes:

- `data/` and `uploads/` are symlinked onto the disk at `/var/data`. On first boot an empty disk is seeded from the committed `content.json` and images; existing files are never overwritten.
- Persistent disks need a paid Render instance type. On the free plan, admin edits and uploads are lost on each redeploy.
- `.htaccess` is not read in the container. `docker/portfolio.conf` blocks `/data` and `/inc` and PHP execution in `/uploads`.
- `config.json` (the password hash) is not baked into the image, so you set the password once after the first deploy. Sessions reset when the service restarts.
- Uploads are capped at 5 MB in the app; PHP is set to 6 MB (`docker/php-overrides.ini`).

To try it locally with Docker:

```
docker build -t enzo-profile .
docker run --rm -p 10000:10000 -v enzo-data:/var/data enzo-profile
```

## Not ported from the Node version

The chat widget and visitor analytics. Say the word if you want those in PHP too.
