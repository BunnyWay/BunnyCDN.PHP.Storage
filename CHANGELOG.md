# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Support for download/upload content in memory with `Client::getContents()` and `Client::putContents()`;

### Changed

- Replaced `ext-curl` with `guzzlehttp/guzzle`, which might use either cURL or PHP streams;

## [2.0.0] - 2023-12-14

### Added

- Composer support;
- Strict types support;
- Minimum PHP version;
- Static analysis checks;