# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.4.0] - 2018-10-18
### Added
- Correct callback logic via Job implementation.


## [1.3.1] - 2018-10-18
### Fixed
- Fixed Set-Cookie name

### Changes
- Updated README for Set-Cookie headers.


## [1.3.0] - 2018-10-17
### Added
- Implemented SetCookieRating #32

### Fixed
- Fixed deprecation error in PHP 7.2
- Fixed parent constructor call.
- Fixed missing php dependency in Dockerfile


## [1.2.1] - 2018-10-11
### Fixed
- Fixed deprecation error `INTL_IDNA_VARIANT_2003 is deprecated`. <br>
[Further Information](https://bugs.php.net/bug.php?id=75609)


## [1.2.0] - 2018-10-10
### Fixed
- Fixed #51

### Added
- Support for domains with umlauts

### Changed
- Upraded to Laravel 5.7
- SpeedUp PHPUnit tests


## [1.1.0] - 2018-10-01
### Added
- `Referrer-Policy` header rating


## [1.0.2] - 2018-09-14
### Fixed
- Bugs in ContentTypeRating when only the `meta` tags are set.
- Rating of sources and sinks with comments (#41).

### Changed
- Upgraded `voku/simple_html_dom` to actual version.


## [1.0.1] - 2018-09-12
### Fixed
- Bugs in ContentTypeRating when only the `meta` tags are set.
- Rating of sources and sinks with comments (#41).

### Changed
- Upgraded `voku/simple_html_dom` to actual version.


## [1.0.0] - 2018-09-07
### Added
- CHANGELOG.md and semantic versioning

[Unreleased]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.4.0...development
[1.4.0]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.3.1...1.4.0
[1.3.1]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.2.0...1.3.0
[1.2.1]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.0.2...1.1.0
[1.0.2]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/SIWECOS/HSHS-DOMXSS-Scanner/compare/1.0.0...1.0.1
