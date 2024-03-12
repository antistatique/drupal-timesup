# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- add tests with drupal 10.3

## [2.0.1] - 2024-03-01
### Fixed
- fix usage of deprecated getMockBuilder by createMock
- fix phpcs use statements should be sorted alphabetically - Issue #3373568 by nitin_lama, roshni27, aayushmankotia, Satish_kumar, wengerk
- add missing call to parent::setUp() on tests
- fix deprecation creation of dynamic property

### Added
- add Drupal GitlabCI
- add coverage of Drupal 10.2.x
- add coverage of Drupal 11.0-dev

### Removed
- drop tests support on Drupal <= 9.4

## [2.0.0] - 2022-12-16
### Changed
- new Dev branch following the new new tag semver of Drupal

## [1.1.0] - 2022-12-16
### Fixed
- fix Issue #3318856 by solantoast: The 'core_version_requirement' constraint (^9) requires the 'core' key not be set
- fix call to deprecated method setMethods()
- fix passing null to parameter #1 () of type string is deprecated

### Added
- add support Drupal 9.5
- add official support of drupal 9.5 & 10.0

### Removed
- drop support of drupal below 9.3.x

### Changed
- re-enable PHPUnit Symfony Deprecation notice

## [1.0.0] - 2022-10-21
### Added
- add midnight resolver
- add dependabot for Github Action dependency
- add support Drupal 9.4 & 9.5
- add upgrade-status check

### Changed
- replace drupal_ti by wengerk/drupal-for-contrib
- move changelog format in order to use Keep a Changelog standard

### Removed
- disable symfony deprecations helper on phpunit
- drop support Drupal 8.8
- remove satackey/action-docker-layer-caching on Github Actions
- drop support of drupal below 9.0

## [1.0.0-alpha1] - 2020-03-27
### Added
- init module

[Unreleased]: https://github.com/antistatique/drupal-timesup/compare/2.0.1...HEAD
[2.0.1]: https://github.com/antistatique/drupal-timesup/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/antistatique/drupal-timesup/compare/8.x-1.1...2.0.0
[1.1.0]: https://github.com/antistatique/drupal-timesup/compare/8.x-1.0...8.x-1.1
[1.0.0]: https://github.com/antistatique/drupal-timesup/compare/8.x-1.0-alpha1...8.x-1.0
[1.0.0-alpha1]: https://github.com/antistatique/drupal-timesup/releases/tag/8.x-1.0-alpha1
