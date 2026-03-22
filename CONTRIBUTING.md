# Contributing

Thank you for considering a contribution to `laravel-passwordless`! This document outlines the process and expectations for contributing.

---

## Code of Conduct

Please be respectful and considerate in all interactions. We follow the [Contributor Covenant](https://www.contributor-covenant.org/) code of conduct.

---

## Reporting Bugs

Before opening a bug report, please:

1. Search [existing issues](https://github.com/wiredrhino/laravel-passwordless/issues) to avoid duplicates.
2. Reproduce the issue on the **latest** version of the package.

When filing a bug report, include:

- PHP and Laravel versions
- A minimal reproduction (failing test or code snippet)
- The exact error message or unexpected behaviour
- Steps to reproduce

---

## Suggesting Features

Open a [GitHub Discussion](https://github.com/wiredrhino/laravel-passwordless/discussions) or issue with the `enhancement` label. Describe:

- The problem you're trying to solve
- Your proposed solution or API
- Any alternatives you've considered

---

## Submitting Pull Requests

### 1. Fork & branch

```bash
git clone https://github.com/wiredrhino/laravel-passwordless.git
cd laravel-passwordless
git checkout -b feat/my-feature
```

### 2. Install dependencies

```bash
composer install
```

### 3. Make your changes

- Follow existing code style (PSR-12 via Laravel Pint).
- Add or update tests for any changed behaviour.
- Keep commits focused and descriptive.

### 4. Run the test suite

```bash
composer test
```

All tests must pass before submitting.

### 5. Run static analysis

```bash
composer analyse
```

Fix any PHPStan / Larastan errors introduced by your changes. If you must suppress a third-party false positive, add it to `phpstan-baseline.neon` with a comment explaining why.

### 6. Fix code style

```bash
composer format
```

### 7. Open the pull request

- Target the `main` branch.
- Describe what changed and why.
- Reference any related issues (e.g. `Closes #42`).
- Keep the PR focused — one logical change per PR.

---

## Development Setup

The package uses [Orchestra Testbench](https://github.com/orchestral/testbench) for a self-contained Laravel environment. No separate application is needed.

| Command | Description |
|---|---|
| `composer test` | Run the full Pest test suite |
| `composer test-coverage` | Run tests with HTML coverage report |
| `composer analyse` | Run PHPStan / Larastan static analysis |
| `composer format` | Auto-fix code style with Laravel Pint |

---

## Tests

Tests live in `tests/` and are written with [Pest v4](https://pestphp.com/).

- **Unit tests** cover models, actions, notifications, and support classes.
- **Feature/integration tests** cover HTTP controllers and the full request lifecycle.
- **Architecture tests** (`ArchTest.php`) enforce structural rules.

When adding a new feature, add corresponding tests. When fixing a bug, add a test that would have caught it.

---

## Coding Style

This project follows the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard, enforced by [Laravel Pint](https://laravel.com/docs/pint) with the `laravel` preset. Run `composer format` before committing.

---

## Security Vulnerabilities

**Do not open a public issue for security vulnerabilities.**

Please review [our security policy](../../security/policy) and report vulnerabilities responsibly.

---

## Changelog

Please update `CHANGELOG.md` under an `[Unreleased]` section when your PR introduces user-facing changes. Follow the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format.

---

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE.md).

