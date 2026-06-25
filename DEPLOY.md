# GMS — Deployment Guide (GitHub → Hostinger, automated)

Push code to `main` → GitHub Actions builds the CSS and deploys to Hostinger over FTPS. No manual upload after setup.

---

## One-time setup

### 1. Push the project to GitHub
Double-click **`git-push.bat`** (in the project root). It will:
1. Build the production CSS (`npm run build`)
2. Run `verify.js` (aborts if any file is corrupt — never pushes broken data)
3. Commit everything
4. Push to `origin/main` → https://github.com/Murvanidz3/gmsholding

> First push only: if GitHub rejects it because the remote already has a commit, run once in the folder:
> `git pull origin main --allow-unrelated-histories` then double-click `git-push.bat` again.

### 2. Get your Hostinger FTP credentials
hPanel → **Files → FTP Accounts**. Note:
- **FTP hostname** (e.g. `ftp.yourdomain.com` or the server IP)
- **FTP username**
- **FTP password**
- **Directory** — usually `/public_html/` (or `/domains/yourdomain.com/public_html/`)

### 3. Add the credentials as GitHub Secrets
GitHub repo → **Settings → Secrets and variables → Actions → New repository secret**. Add four:

| Secret name      | Value                                  |
|------------------|----------------------------------------|
| `FTP_HOST`       | your FTP hostname / IP                  |
| `FTP_USERNAME`   | your FTP username                      |
| `FTP_PASSWORD`   | your FTP password                      |
| `FTP_SERVER_DIR` | `/public_html/` (note trailing slash)  |

That's it. Every push to `main` now auto-deploys. Watch progress under the repo's **Actions** tab.

---

## First deploy — two manual one-time steps on the server
The pipeline deliberately **does not** push runtime data, so the admin's live edits are never overwritten. On the very first deploy these don't exist on the server yet, so do this once via hPanel **File Manager** (or FTP):

1. **Upload the seed data:** copy local `data/site_content.json` into `public_html/data/` on the server.
2. **Create writable folders:** ensure these exist and are writable (permission `755` or `775`):
   - `public_html/data/`
   - `public_html/assets/img/uploads/`

After this, the admin panel manages content on the server and future pushes never touch it.

---

## What gets deployed vs. preserved

**Deployed (overwritten each push):** `index.php`, `includes/`, `sections/`, `admin/`, `assets/css/`, `assets/js/`, `assets/img/` (except uploads).

**Never touched by deploy (server keeps its own):**
- `data/**` — the JSON content store (admin edits live here)
- `assets/img/uploads/**` — admin-uploaded images
- dev files: `src/`, `node_modules/`, `*.bat`, `*.md`, `verify.js`, `package*.json`, `tailwind.config.js`

---

## Production config checklist
- **`includes/config.php` → `BASE_URL`:** leave `''` if the site is at the domain root (`public_html`). If you deploy into a subfolder (e.g. `public_html/gms`), set `define('BASE_URL', '/gms');`.
- **Admin password:** change the bcrypt hash in `admin/includes/auth.php` before going live. Generate a new one locally: `php -r "echo password_hash('YOUR_PASSWORD', PASSWORD_DEFAULT);"`
- **HTTPS:** enable SSL in hPanel; the FTPS deploy and `referrerpolicy` already assume https.

---

## Alternative: Hostinger native Git (no GitHub Actions)
If you prefer Hostinger to pull directly: hPanel → **Advanced → Git** → add repository `https://github.com/Murvanidz3/gmsholding.git`, branch `main`, install path `public_html`. Because the compiled `assets/css/style.css` is committed, the site works with a plain pull — no build step needed. Enable "Auto deployment" and add the webhook URL to GitHub (repo → Settings → Webhooks) for push-to-deploy. (The GitHub Actions route above is preferred — it rebuilds CSS and runs the integrity check.)
