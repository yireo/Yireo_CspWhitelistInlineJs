# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 26 November 2026
### Added
- Admin warning when CSP nonces may be cached by the Full Page Cache
- DI argument `allowedBlocks` of `AllowBlock` to whitelist specific blocks

### Changed
- BREAKING: Only static inline scripts literally present in the template of a whitelisted block are whitelisted; child blocks and scripts containing PHP output are no longer whitelisted
- BREAKING: The block `head.additional` is no longer whitelisted by default
- Whitelist mode `all` is restricted to the global scope and ignored in production mode, unless `csp_whitelist_inline_js/allow_all_in_production` is enabled in env.php
- Require yireo/magento2-csp-utilities ^1.1.0
- Document template-only whitelisting, the DI allowlist and caching in the README

### Fixed
- Script logger no longer breaks rendering, logs paths relative to the Magento root and locks the log file against concurrent requests

## [0.1.2] - 06 November 2026
### Fixed
- Add type hints to code
- Pass trim() characters explicitly in ScriptLogger

## [0.1.1] - 2 October 2024
### Fixed
- Prevent null HTML from causing errors

## [0.1.0] - 4 September 2024
### Added
- New default mode `custom` to only allow blocks that are explicitly whitelisted
- Change logging so that entries are de-duplicated and sorted

## [0.0.3] - 2 August 2024
### Fixed
- Add a logger for analysing which scripts are fixed

## [0.0.2] - 4 July 2024
### Fixed
- Extend from SecureHtmlRenderer\Proxy to delay the instantiation @hostep

## [0.0.1] - 28 June 2024
### Added
- Initial release
