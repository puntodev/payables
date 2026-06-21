# Changelog

All notable changes to `payables` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Released entries below are maintained automatically from the GitHub release notes
(see `.github/workflows/update-changelog.yml`); the `Unreleased` section tracks the
range of changes on `main` that have not been released yet.

## [Unreleased]

### 🧰 Maintenance & Dependencies

- Require PHP 8.4+, upgrade to Laravel 12 and `puntodev/mercadopago` 6, and update
  dependencies (#13). **BREAKING:** the package now requires PHP `>=8.4` and Laravel 12.

### 📚 Documentation

- Add a README usage guide and `AGENTS.md` (#13).

### Other Changes

- Modernize the CI workflow and automate changelog updates from GitHub releases (#13).

[Unreleased]: https://github.com/puntodev/payables/commits/main
