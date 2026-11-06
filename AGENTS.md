# Repository Guidelines

## Project Structure & Module Organization
This is a plain PHP application for Podología Ana Ramírez, backed by MySQL/MariaDB. `public/index.php` routes requests through `?accion=...`. Keep request handling in `src/controllers/`, database operations in `src/models/`, and PHP templates in `src/views/` (auth, pacientes, admin, layout). `config/` contains application helpers, authentication/RBAC, and the PDO connection. Static CSS, JavaScript, and images live in `public/assets/`; patient uploads belong in `uploads/pacientes/`. `sql/schema.sql` defines tables and seed data. No test directory currently exists.

## Build, Test, and Development Commands
Run commands from the repository root with PHP, PDO MySQL, and MySQL/MariaDB available:

- `mysql -u root -p < sql/schema.sql` — initialize a disposable development database. This script drops and recreates tables; never run it against live data.
- `php -S 127.0.0.1:8000 -t public` — serve the application at `http://127.0.0.1:8000`.
- `iniciar.bat` — Windows launcher using XAMPP PHP when available; binds to all network interfaces.
- `rg --files -g '*.php' -0 | xargs -0 -n 1 php -l` — check PHP syntax.

There is no build step, package manifest, configured formatter, or standalone lint tool. Tailwind CSS and Font Awesome load through CDNs.

## Coding Style & Naming Conventions
Use four-space indentation and match neighboring code. Keep `declare(strict_types=1);` in PHP application logic, typed parameters/returns, and class/method opening braces on separate lines. Use PascalCase classes (`PacienteController`), Spanish camelCase methods/variables (`obtenerTodos`), and snake_case database fields (`fecha_creacion`). Escape template output with `e()` and use prepared PDO statements.

## Testing Guidelines
No automated testing framework or coverage target is configured. Run syntax checks and manually verify login, password changes, patient registration/search/details, permitted edits, soft deletion, and print views using synthetic data. Check allowed and denied actions for each affected role and confirm audit entries capture changes. Include validation steps and results in the PR.

## Commit & Pull Request Guidelines
This checkout has no Git metadata, so existing commit conventions cannot be verified. Use concise, imperative subjects, for example `Fix patient search pagination`. Keep changes focused. PRs should explain behavior, link relevant issues, document schema/configuration changes, and include screenshots for UI updates.

## Security & Configuration
Configure connections with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `DB_CHARSET`. Keep credentials and real patient data out of commits. Preserve server-side role checks, soft deletion, and audit history.
