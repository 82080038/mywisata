# Windsurf Project Rules — Tour Guide Application

## Project Type
PHP Native MVC web application (no framework, no Composer required)

## Key Rules

### Code Generation
- Always use PDO prepared statements with named parameters (`:param`), never string concatenation
- Every controller must call `Middleware::requireRole()` in constructor if role-restricted
- Every POST form must include `<input type="hidden" name="csrf_token" value="<?= CSRF_TOKEN ?>">`
- Every view must escape output with `Helper::e($var)` or `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- Every API endpoint returns JSON via `$this->json(['status' => 'success|error', 'message' => '...', 'data' => ...])`
- Every model extends `Model` base class and uses `$this->db->query($sql, $params)` pattern
- File uploads must validate MIME type, extension, and size (max 5MB)
- Passwords must be hashed with `password_hash($pass, PASSWORD_BCRYPT)`, never plain text

### File Organization
- Controllers: `app/controllers/PascalCaseController.php`
- Models: `app/models/PascalCaseModel.php`
- Views: `app/views/role/snake_case_view.php`
- Assets: `public/assets/css/` or `public/assets/js/`
- SQL: `database/migration.sql` (schema), `database/seed.sql` (data)

### Naming
- Class: PascalCase (`TourGuideController`)
- Method: camelCase (`getByGuide`)
- Variable: camelCase (`$bookingId`)
- Constant: UPPER_SNAKE (`BASE_URL`)
- Table: snake_case (`tour_guides`)
- Column: snake_case (`user_id`)

### Documentation References
- Architecture: `docs/03_DESAIN_ARSITEKTUR_APLIKASI.md`
- Database: `docs/05_DESAIN_DATABASE_MYSQL_ERD.md`
- API: `docs/21_API_DESIGN_AJAX_JSON.md`
- Security: `docs/20_SECURITY_SYSTEM.md`
- Coding Standards: `docs/28_STANDAR_KODE_KONTRIBUSI.md`
- Checklist: `docs/29_CHECKLIST_PENGEMBANGAN.md`

### Do NOT
- Do not use `var` in JavaScript (use `const` or `let`)
- Do not use short PHP tags `<?` (always use `<?php`)
- Do not hardcode database credentials in controllers/models
- Do not send password in API response
- Do not use `alert()` in JavaScript (use SweetAlert2 `Swal.fire()`)
- Do not use inline SQL concatenation (`"WHERE id = $id"`)
- Do not commit `.env`, `config.local.php`, `logs/`, or `uploads/`
