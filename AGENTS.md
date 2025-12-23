# Repository Guidelines

## Project Structure & Module Organization
- `src/`: Symfony bundle source code (PSR-4 `Solitus0\HttplugLoggerBundle\`).
- `src/Parser/`, `src/Monolog/`, `src/LevelPicker/`, `src/DependencyInjection/`: core modules.
- `src/Resources/config/services.yaml`: default service wiring.
- No `tests/` directory is present currently; add new tests under `tests/` when introduced.

## Build, Test, and Development Commands
- `make phpstan`: run static analysis and write a report to `var/phpstan/phpstan-report.json`.
- `make ecs`: run Easy Coding Standard with auto-fix.
- `make rector`: run Rector automated refactors.
- `make all`: run `phpstan`, `ecs`, then `rector`.
- `make patch` / `make minor`: bump version via `bump_version.py`.

## Coding Style & Naming Conventions
- PHP 8.1+ codebase; follow PSR-4 namespaces (`Solitus0\HttplugLoggerBundle\...`).
- Use 4-space indentation in PHP files.
- Keep classes focused by module: parsers in `src/Parser/`, processors/formatters in `src/Monolog/`.
- Formatting is enforced via ECS (`ecs.php`); run `make ecs` before opening a PR.

## Testing Guidelines
- PHPUnit is available (`phpunit/phpunit`), but there are no existing tests yet.
- When adding tests, place them under `tests/` and use `*Test.php` naming.
- Suggested run command once tests exist: `vendor/bin/phpunit`.

## Commit & Pull Request Guidelines
- Recent commit messages are short and informal (e.g., “in p”, “first commit”); no strict convention is established.
- For new work, prefer clear, imperative messages (e.g., `Add request parser for GraphQL`).
- PRs should include: a brief summary, key changes list, and any config updates (e.g., `services.yaml`).

## Configuration & Integration Notes
- This is a Symfony bundle for HTTPlug + Monolog; see `README.md` for configuration examples.
- If you add new services, update `src/Resources/config/services.yaml` and keep identifiers consistent with the existing naming patterns.
