# CHANGELOG

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com).

## [0.3.1] - 2019-02-19

### Fixed

* Decimal representation of histogram buckets (which caused the python parser to fail)

## [0.3.0] - 2018-11-22

### Fixed

* Counters must always be exposed with `<metric_name>_total` - [#2]

### Added

* Integration tests to check metrics output against [openmetrics' python parser](https://github.com/prometheus/client_python/blob/master/prometheus_client/openmetrics/parser.py) - [#3]

## [0.2.0] - 2018-11-16

### Added

* Support for PHP >= 7.1 - [#1]

## [0.1.0] - 2018-11-04

First stable release.

[0.3.1]: https://github.com/openmetrics-php/exposition-text/compare/v0.3.0...v0.3.1
[0.3.0]: https://github.com/openmetrics-php/exposition-text/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/openmetrics-php/exposition-text/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/openmetrics-php/exposition-text/tree/v0.1.0

[#3]: https://github.com/openmetrics-php/exposition-text/issues/3
[#2]: https://github.com/openmetrics-php/exposition-text/issues/2
[#1]: https://github.com/openmetrics-php/exposition-text/issues/1
