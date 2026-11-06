# Kim Ralfonzo Diwa (Enzo) — Portfolio & CMS

> *"I design and build clean, working websites, from environmental data platforms to ticketing systems. If you can sketch it, I can ship it."*

Public portfolio site (`index.php`) featuring smooth scroll reveals, dynamic projects showcase, client reviews, and a lightweight private admin panel at `/admin/`. Built with clean PHP 8.0+ — no heavy frameworks, no external database, and no Composer dependencies.

---

## 👤 About the Developer

- **Name:** Kim Ralfonzo Diwa (Enzo)
- **Role:** Freelance Web Developer & Full-Stack Developer
- **Email:** [kimralfonzodiwa@gmail.com](mailto:kimralfonzodiwa@gmail.com)
- **Specialties:** Data platforms, management & ticketing systems, responsive SPAs, custom PHP backends.
- **Tech Stack:**
  - **Development:** PHP 8.x, JavaScript (ES6+), Vue.js, Node.js, MySQL, HTML5, CSS3, Git
  - **Design & UI:** Figma, Responsive UI Design, Prototyping
- **Featured Projects:**
  - **MAPS (Mangrove Analytics & Prediction System):** Environmental platform predicting mangrove survivability and population growth.
  - **Stellaris (ECONYPA):** Modern single-page app promoting sustainable use of nipa and local resources.
  - **RSCI Ticketing System:** Internal issue-tracking and workflow resolution platform.

---

## 🎨 Personalizing the Portfolio

To customize personal branding, bio, skills, and content across the portfolio:

### 1. Profile, Bio & Contact Info (`index.php`)
- **Name & Initials:** Update `<span class="word">Kim Ralfonzo</span>` / `Diwa` (lines 65–67) and the `KRD.` brand mark (lines 37, 42).
- **Tagline & Lede:** Customize the intro summary in `<p class="lede">` (line 69).
- **Profile Photo:** Replace `assets/profile-photo.webp` with your own image, or update the `src` attribute on line 70.
- **Contact Email:** Update the `$email` variable near the top of [index.php](file:///c:/Users/Kim%20Diwa/OneDrive/Documents/GitHub/EnzoProfile/index.php#L11):
  ```php
  $email = 'your.email@example.com';
  ```

### 2. Skills & Tech Stack (`index.php`)
Edit the skill chip arrays under `<section id="skills">` (lines 160–169) to reflect your current stack:
```php
<?php foreach (['HTML5','CSS3','JavaScript','PHP','Vue.js','Node.js','MySQL','Git'] as $s): ?>
```

### 3. Experience & Education (`index.php`)
Update work history, project roles, and your university/degree details under `<section id="experience">` (lines 172–179).

### 4. Projects & Reviews (`/admin/` or `data/content.json`)
- Visit `http://localhost:8000/admin/` to add, edit, reorder, or upload screenshots for projects and testimonials.
- All entries are saved directly in `data/content.json`, and uploaded media is stored in `uploads/`.

### 5. Color Theme & Styling (`assets/style.css`)
Customize the theme variables at the top of [assets/style.css](file:///c:/Users/Kim%20Diwa/OneDrive/Documents/GitHub/EnzoProfile/assets/style.css#L1):
```css
:root {
  --accent: #D9FF3D; /* Neon lime accent */
  --bg:     #0F120D; /* Dark background */
  --bg2:    #121509;
  --card:   #141810;
  --ink:    #F2F4EC; /* Text color */
  --muted:  #9BA294; /* Subdued text */
}
```

---

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
