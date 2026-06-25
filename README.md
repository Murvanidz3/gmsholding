# GMS Construction — PHP + Tailwind

## Stack
Pure PHP includes · Tailwind CLI (compiled, no CDN) · Vanilla JS · Swiper.js (CDN).

## Structure
```
gmsholding/
├── index.php               # Home (includes header + hero + footer)
├── tailwind.config.js
├── package.json
├── src/input.css           # Tailwind source (base/components/utilities + overrides)
├── includes/
│   ├── config.php          # Site data + $hero_slides  ← Phase 2 admin writes here
│   ├── header.php          # Topbar, sticky nav, mobile drawer
│   └── footer.php          # Footer, back-to-top, script tags
├── sections/               # Phase 2 modular sections (about, services, projects…)
└── assets/
    ├── css/style.css       # COMPILED output (committed for shared hosting)
    ├── js/main.js          # Swiper init, sticky header, reveal, drawer
    └── img/
```

## Tailwind CLI setup
```bash
npm install                 # installs tailwindcss devDependency
npm run dev                 # watch + recompile to assets/css/style.css
npm run build               # one-off minified production build
```
No Node needed on the server — commit `assets/css/style.css` and deploy. Hostinger only serves PHP + static files.

## Run locally
Point XAMPP/Laragon docroot at this folder (or `php -S localhost:8000`), then open `index.php`.
Set `BASE_URL` in `includes/config.php` if running from a subdirectory.

## Phase 2 hook
All content lives in `$site`, `$nav`, `$hero_slides` in `config.php`. The admin panel
swaps that array for DB/JSON — markup stays untouched.
