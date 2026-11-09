# Alserb Medical website

A bilingual Arabic and English storefront for a Libyan importer of new medical equipment. It includes a public catalog, a session-based order list, order requests stored in MySQL, order-status emails, tracking history, and an admin panel for orders, inquiries, the catalog, and contact settings. There is no online payment and no customer account: every order is confirmed manually by staff.

## Requirements

- PHP 8.1 or newer with PDO MySQL
- MySQL 8.0 or newer, managed on this machine by **DBngin**
- An SMTP server for order emails. The local default is Mailpit on `127.0.0.1:1025`.
- A web server that can point its document root at `public/` (Apache is configured through `public/.htaccess`)

This project is configured for the DBngin MySQL instance on `127.0.0.1:3306`. Do not start a Homebrew MySQL server alongside it.

## Local setup

1. Start MySQL in DBngin. The default DBngin connection is `127.0.0.1:3306`, user `root`, empty password.
2. Start Mailpit if order emails should be captured locally (SMTP `127.0.0.1:1025`, web UI `http://127.0.0.1:8025`).
3. Copy the config and check the database, URL, and mail blocks:

```bash
cp app/config.example.php app/config.local.php
# DBngin defaults: host 127.0.0.1, port 3306, user root, password ''
# app.url should be the public base URL, e.g. https://example.com
# mail.host/port point to Mailpit locally
php bin/install.php --fresh --password='choose-a-password'
php -S localhost:8080 router.php
```

Open <http://localhost:8080>. The root redirects to `/ar`; the English site is under `/en`.

The installer creates the database named in `app/config.local.php`, applies `database/schema.sql`, and seeds:

- 6 departments and 12 generic equipment items
- 2 sample quotation requests and 1 sample order
- contact details (`0925100039`, Benghazi — Venice Street; `info@example.com` remains a placeholder)

Use `--fresh` only when you want to erase the existing tables and start again.

### Admin access

The seeded staff account for this local build is:

```text
URL:      /admin/login
Username: admin
Password: Alserb!2026
```

The password is set at install time and appears only in this setup note. Change it with:

```bash
php bin/password.php --username=admin --password='new password'
```

`app/config.local.php` is excluded by `.gitignore`, so database credentials stay out of version control.

## Site map

| Page | Arabic | English |
| --- | --- | --- |
| Home | `/ar` | `/en` |
| About | `/ar/about` | `/en/about` |
| Equipment | `/ar/equipment` | `/en/equipment` |
| Equipment item | `/ar/equipment/{slug}` | `/en/equipment/{slug}` |
| How supply works | `/ar/supply` | `/en/supply` |
| Contact / quotation | `/ar/contact` | `/en/contact` |
| Order list | `/ar/cart` | `/en/cart` |
| Checkout | `/ar/checkout` | `/en/checkout` |
| Order status | `/ar/order/{token}` | `/en/order/{token}` |
| Track an order | `/ar/track` | `/en/track` |
| Image credits | `/ar/credits` | `/en/credits` |
| Admin | `/admin` | `/admin` |

Equipment pages accept `?category={slug}` for the department filter and `?q={term}` for the catalog search. The contact page accepts `?product={slug}` to preselect an item on the quotation form. Order links use a random token; customers can also reach the order page from the footer by entering the order number and the email used on the order.

## Search engines

- `robots.txt` and `sitemap.xml` are generated from the database and the configured base URL (`app.url`). The sitemap lists every static page, department filter, and active product in both languages, with `hreflang` alternates.
- Every public page outputs a unique title, description, keywords, canonical URL, `hreflang` links for Arabic, English, and `x-default`, Open Graph tags, and Twitter card tags.
- Structured data is emitted as JSON-LD: `MedicalBusiness` and `WebSite` site-wide, `Product` and `BreadcrumbList` on equipment pages, and `CollectionPage` with an `ItemList` on the catalog.
- Catalog search results (`?q=`) are `noindex, follow`; cart, checkout, order status, and tracking pages are `noindex, nofollow`. Admin pages are excluded in `robots.txt`.
- Set `app.url` to the final public origin (for example `https://alserbmedical.com`) so canonical, Open Graph, sitemap, and email links are absolute and correct.

## Admin panel

The admin interface is English. Product, department, and page-text fields are paired Arabic and English. Public URLs are generated automatically from the English name; staff never edit slugs.

- **Orders**: search and filter order requests, open the customer view, change status (pending, accepted, processing, payment pending, payment completed, shipping, delivered, completed, rejected, cancelled), add a customer-visible update and an internal note, and email the customer on each status change
- **Equipment**: search, create, edit, hide/show, change sort order, replace or remove an image
- **Departments**: search, create, edit, hide/show; hiding a department also hides its equipment
- **Quotation requests**: search, list, open, and set status to new, read, or closed
- **Settings**: phone, email, WhatsApp, address, city, timezone, home introduction, and about introduction

The public order flow stores the customer details and an item snapshot, clears the cart, shows a token-protected status page with a full tracking timeline, and emails the customer an order link. No payment step is present.

Uploaded images are checked for JPEG/PNG/WebP type and a 4 MB limit, stored in `public/uploads/` with random names, and never executed. Forms use CSRF tokens; output is escaped. Admin sessions use `password_hash` / `password_verify`.

## Email and security

- Order emails are sent through the SMTP settings in `app/config.local.php`. On order creation and every status change the customer receives an email with the reference, items, status, and a link to the tracking page. The admin address (`mail.admin_to`, not the public contact setting) is copied as BCC.
- `app.url` must be the public site URL so email links open the right host.
- Application-level rate limits protect the login (8 attempts / 5 minutes per IP), contact form, checkout, tracking lookup, cart actions, and general traffic (300 requests / minute per IP). Responses return HTTP 429 and `Retry-After`.
- This rate limiter is a first layer only. For real production traffic, place the site behind a reverse proxy, CDN, or WAF that also filters abusive traffic. If a proxy is used, the app must be configured to trust its forwarded client address.

## Structure

```text
router.php              PHP built-in server router and static file handler
public/index.php        Front controller
public/.htaccess        Apache rewrite to index.php
app/Router.php          Small pattern router
app/routes.php          Route table
app/PublicSite.php      Public pages, cart, checkout, orders, tracking, quotation handling
app/Admin.php           Admin login, orders, catalog, inquiries, settings
app/Mailer.php          SMTP order notification mailer
app/helpers.php         Escaping, translation, CSRF, uploads, settings, rate limits
app/lang/{en,ar}.php    UI strings
app/views/              Public, admin, and email templates
database/schema.sql     Schema and seed data
bin/install.php         Database installer
bin/password.php        Staff password reset
public/assets/          Fonts, photography, CSS, JavaScript
public/uploads/         Admin-uploaded catalog images
```

## Content and design notes

- Copy is deliberately short and factual. It states that the company imports and supplies **new** equipment and does not trade in used devices or spare parts.
- No registration numbers, certificates, manufacturer authorizations, client names, testimonials, or statistics are invented.
- No equipment brands or model specifications are named. Product pages say details are confirmed in the quotation.
- The phone and Benghazi address are the details supplied by the client. The email, the two seeded inquiries, and the seeded order are placeholders to be replaced in the admin panel.
- No online payment is taken. Sending the order list creates a pending request; staff change the status to accepted, processing, payment pending, payment completed, shipping, delivered, completed, rejected, or cancelled after confirming availability and terms.
- Arabic is Modern Standard Arabic. Direction is set with `dir`/`lang` and the stylesheet uses logical properties, so one file serves both directions.
- Typography is Instrument Sans for Latin, IBM Plex Sans Arabic for Arabic, and IBM Plex Mono for catalog labels and references. The palette follows the supplied Alserb Medical logo: deep navy, medical blue, and cyan accents on a cool paper background.
- The logo and wordmark are supplied by Alserb Medical. The exact horizontal lockup is used in the header, admin panel, login screen, and order emails; a white version of the full lockup is used in the footer.
- Photography includes a hero image, one image per department, and distinct product photographs for the seeded equipment. No text is overlaid on images.

## Photography credits

The Alserb Medical logo is supplied by the client and is stored in `public/assets/img/logo-lockup.png` (header/admin/email), `logo-full-white.png` (footer), and `favicon.svg` / `favicon-32.png` / `favicon-180.png`.

Used for illustration only. Images were resized, cropped, and edited where needed to remove equipment markings.

**Unsplash** (Unsplash License, <https://unsplash.com/license>)

- Operating theatre category and operating table — photo-1516549655169-df83a0774514
- Laboratory and laboratory analyser — photo-1579154204601-01588f351e67

**Pexels** (Pexels License, <https://www.pexels.com/license/>)

- Hero operating theatre — photo 36101264
- Bedside patient monitor — photo 3845129
- Central monitoring station — photo 11198232
- Diagnostic ultrasound system — photo 7089029
- Electrocardiograph system — photo 9408868
- Laboratory centrifuge — photo 6627667
- Steam sterilizer — photo 6627665
- Sterilization cabinet — photo 8413159
- Hospital bed — photo 12081340
- Examination couch — photo 7789609

**Wikimedia Commons**

- `diagnostics.jpg` — “A medical ultrasound linear array probe, scan head, transducer” by Harrison Keely, CC BY 4.0, <https://commons.wikimedia.org/wiki/File:A_medical_ultrasound_linear_array_probe,_scan_head,_transducer.jpg>
- `surgical-ceiling-light.jpg` — “Lampada scialitica” by Glaucolongoni, CC BY-SA 4.0, <https://commons.wikimedia.org/wiki/File:Lampada_scialitica.jpg>

The public credits page repeats these attributions.

## Deployment notes

- Point the web server document root at `public/`, not the project root.
- Keep `app/config.local.php` outside version control and restrict access to it.
- Ensure `public/uploads/` is writable by PHP and that PHP execution there stays disabled.
- For Apache, `mod_rewrite` must be enabled so clean URLs work.
- With the PHP built-in server, run `php -S localhost:8080 router.php` from the project root. The router serves files from `public/` and blocks PHP source files.

## Check before calling it done

1. Both languages load and the layout mirrors correctly.
2. A narrow viewport keeps navigation, forms, and tables usable.
3. Adding to the cart, changing quantities, removing an item, and sending an order request all work.
4. The order appears in the admin panel as pending, and changing its status is visible on the customer order page and its tracking timeline.
5. Order-created and status-change emails arrive with a working tracking link.
6. The footer tracking form and `/track` page find an order by number and email.
7. The quotation form saves a row in `inquiries` and shows the confirmation panel.
8. A product can be created with an automatic slug, then edited, hidden, and shown.
9. A department can be hidden and its items disappear from the public catalog.
10. Admin search works for orders, equipment, departments, and quotation requests.
11. Rate limiting returns 429 after the configured number of requests.
12. Contact settings changed in the admin panel appear on the public site.
13. No public URL exposes `index.php` or allows PHP execution in `uploads/`.
