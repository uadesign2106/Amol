# Green Plus Website (www.greenpluspune.com)

Production-ready enquiry-based website in PHP + MySQL (no cart / payment).

## Customer vs Admin Views
- **Customer View**: public pages (`/index.php`, `/products.php`, `/services.php`, `/contact.php`, `/about.php`) without login.
- **Admin View**: only under `/admin`.
  - `/admin` redirects to login if not authenticated.
  - All admin modules are session-protected with RBAC permission checks.

## Features
- Responsive multilingual frontend (English/Marathi)
- Product listing with category + industry filters
- Per-product enquiry via WhatsApp, Email, and contact form
- Click-to-call and floating WhatsApp on all pages
- Secure admin panel with session auth + role-based permissions
- CRUD: products, categories, industries, enquiries
- User management for Super Admin (create users, assign roles, activate/deactivate, reset passwords)
- Dashboard stats + enquiry status workflow

## RBAC Roles
- **Super Admin**: full access (products/categories/industries/enquiries/users)
- **Admin**: products + enquiries
- **Staff**: enquiries view + status update only

## Setup
1. Copy project to web root (`/var/www/html/greenplus` or similar).
2. Import DB:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Update DB credentials + business contact in `app/config/config.php`.
4. Ensure writable permission on `admin/uploads/`.
5. Serve with Apache/Nginx + PHP 8.1+.

## Admin Login
- URL: `/admin`
- Default Super Admin email: `admin@greenpluspune.com`
- Default password: `admin123`

> Change password immediately after first login.

## Security
- Password hashing (`password_hash`/`password_verify`)
- Prepared statements for DB operations
- CSRF token on admin and enquiry writes
- Session-based admin auth (`isLoggedIn()`)
- Permission middleware (`hasPermission()`) for every admin module/action
