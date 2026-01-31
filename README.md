Product Inventory System — README

Login credentials
- Username: anmol
- Password: admin123

Setup instructions
1. Install XAMPP (Apache + MySQL) and start Apache and MySQL services.
2. Place the project folder inside XAMPP's `htdocs` (already at `c:/xampp/htdocs/FSD_FINAL`).
3. Create the database:
   - Open phpMyAdmin (http://localhost/phpmyadmin) or use MySQL CLI.
   - Import `config/database_schema.sql` to create the `product_inventory` schema and seed data.
4. Configure database connection if needed:
   - Review `config/db.php`. By default it uses:
     - host: `localhost`
     - username: `root`
     - password: `` (empty)
     - database: `product_inventory`
   - Update those values if your environment differs.
5. Access the app in your browser:
   - http://localhost/FSD_FINAL/public/login.php (or the site root depending on your server config)
6. Optional: enable `assets/main.js` features are loaded in `includes/footer.php`.

List of features implemented
- User authentication: login, logout, session handling, password hashing.
- CSRF protection for form submissions (`verifyCSRFToken`, hidden token fields).
- Product CRUD: add, edit, delete products (server-side validation and prepared statements).
- Search: advanced filtering by keyword, category, price range; server-side prepared queries.
- Autocomplete: AJAX endpoint for product name suggestions.
- Profile page: change password with client-side password-match helper.
- Flash messages for success/error notifications.
- Client-side UX: `assets/main.js` provides form checks, autocomplete debounce, numeric guards, delete confirmations, loading helpers, and small UI enhancements.
- Responsive styles and consistent layout via `assets/style.css`.


