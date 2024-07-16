# CHANGELOG

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [0.4.1] - 2024-07-16

[0.4.1]: https://github.com/openmetrics-php/exposition-text/compare/v0.4.0...v0.4.1

### Fixed

* Invalid separation of labels with ", " (comma and space) instead of "," (comma only) — see [#14]

[#14]: https://github.com/openmetrics-php/exposition-text/pull/14

## [0.4.0] - 2024-05-04

[0.4.0]: https://github.com/openmetrics-php/exposition-text/compare/v0.3.1...v0.4.0

* Dropped support for PHP 7.1
* Added support for PHP 8.3
* Removed `HttpResponse` class in order to get rid of the dependency to `psr/http-message` — see [#12]
  * Users must implement or use their own response class to publish the metrics via HTTP
* Fix deprecation warnings for IteratorAggregate since PHP 8.1 — see [#8]
* Moved CI to GitHub Actions

[#12]: https://github.com/openmetrics-php/exposition-text/issues/12

[#8]: https://github.com/openmetrics-php/exposition-text/issues/8

## [0.3.1] - 2019-02-19

[0.3.1]: https://github.com/openmetrics-php/exposition-text/compare/v0.3.0...v0.3.1

### Fixed

* Decimal representation of histogram buckets (which caused the python parser to fail)

## [0.3.0] - 2018-11-22

[0.3.0]: https://github.com/openmetrics-php/exposition-text/compare/v0.2.0...v0.3.0

### Fixed

* Counters must always be exposed with `<metric_name>_total` - [#2]

[#2]: https://github.com/openmetrics-php/exposition-text/issues/2

### Added

* Integration tests to check metrics output against [openmetrics' python parser](https://github.com/prometheus/client_python/blob/master/prometheus_client/openmetrics/parser.py) - [#3]

[#3]: https://github.com/openmetrics-php/exposition-text/issues/3

## [0.2.0] - 2018-11-16

[0.2.0]: https://github.com/openmetrics-php/exposition-text/compare/v0.1.0...v0.2.0

### Added

* Support for PHP >= 7.1 - [#1]

[#1]: https://github.com/openmetrics-php/exposition-text/issues/1

## [0.1.0] - 2018-11-04

[0.1.0]: https://github.com/openmetrics-php/exposition-text/tree/v0.1.0

First stable release.
