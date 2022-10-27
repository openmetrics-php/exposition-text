[![CircleCI](https://circleci.com/gh/openmetrics-php/exposition-text.svg?style=svg)](https://circleci.com/gh/openmetrics-php/exposition-text)
[![Latest Stable Version](https://poser.pugx.org/openmetrics-php/exposition-text/v/stable)](https://packagist.org/packages/openmetrics-php/exposition-text) 
[![Total Downloads](https://poser.pugx.org/openmetrics-php/exposition-text/downloads)](https://packagist.org/packages/openmetrics-php/exposition-text) 
[![codecov](https://codecov.io/gh/openmetrics-php/exposition-text/branch/master/graph/badge.svg)](https://codecov.io/gh/openmetrics-php/exposition-text)
[![phpstan enabled](https://img.shields.io/badge/phpstan-enabled-green.svg)](https://github.com/phpstan/phpstan)

# OpenMetrics-PHP Exposition Text

## Description

Implementation of the text exposition format of [OpenMetrics](https://openmetrics.io).

This implementation supports the following metric types:

* Counter
* Gauge
* Historgram (calculated from a collection of gauges)
* Summary (calculated from a collection of gauges)


**Please note:** that the following content-type header is used for the HTTP response by default 
`Content-Type: application/openmetrics-text; charset=utf-8` as [discussed here](https://github.com/OpenObservability/OpenMetrics/issues/79).

## Requirements

* PHP >= 7.1

## Installation

```bash
composer require openmetrics-php/exposition-text
```

## Usage

### Create a collection of counters and respond it

See [examples/counters.php](./examples/counters.php).

```php
<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetrics\Exposition\Text\Collections\CounterCollection;
use OpenMetrics\Exposition\Text\Collections\LabelCollection;
use OpenMetrics\Exposition\Text\Types\MetricName;
use OpenMetrics\Exposition\Text\Types\Label;
use OpenMetrics\Exposition\Text\Metrics\Counter;

$counters = CounterCollection::fromCounters(
	MetricName::fromString('your_metric_name'),
	Counter::fromValue(1),
	Counter::fromValueAndTimestamp(2, time()),
	Counter::fromValue(3)->withLabels(
		Label::fromNameAndValue('label1', 'label_value')
	),
	Counter::fromValueAndTimestamp(4, time())->withLabels(
		Label::fromNameAndValue('label2', 'label_value')
	)
)->withHelp('A helpful description of your measurement.');

# Add counters after creating the collection
$counters->add(
	Counter::fromValue(5),
	Counter::fromValueAndTimestamp(6, time()),
	Counter::fromValue(7)->withLabels(
		# Create labels from label string
		Label::fromLabelString('label3="label_value"')
	)
);

# Prepare labels upfront
$labels = LabelCollection::fromAssocArray(
    [
    	'label4' => 'label_value',
    	'label5' => 'label_value',
    ]	
);

$counters->add(
	Counter::fromValueAndTimestamp(8, time())->withLabelCollection($labels)
);

var_dump($counters->getMetricLines());
```

#### Prints

```
# TYPE your_metric_name counter
# HELP your_metric_name A helpful description of your measurement.
your_metric_name_total 1.000000
your_metric_name_total 2.000000 1541323663
your_metric_name_total{label1="label_value"} 3.000000
your_metric_name_total{label2="label_value"} 4.000000 1541323663
your_metric_name_total 5.000000
your_metric_name_total 6.000000 1541323663
your_metric_name_total{label3="label_value"} 7.000000
your_metric_name_total{label4="label_value", label5="label_value"} 8.000000 1541323663
```

### Create a collection of gauges and respond it

See [examples/gauges.php](./examples/gauges.php).

```php
<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetrics\Exposition\Text\Collections\GaugeCollection;
use OpenMetrics\Exposition\Text\Collections\LabelCollection;
use OpenMetrics\Exposition\Text\Types\MetricName;
use OpenMetrics\Exposition\Text\Types\Label;
use OpenMetrics\Exposition\Text\Metrics\Gauge;

$gauges = GaugeCollection::fromGauges(
	MetricName::fromString('your_metric_name'),
	Gauge::fromValue(12.3),
	Gauge::fromValueAndTimestamp(-45.6, time()),
	Gauge::fromValue(78.9)->withLabels(
		Label::fromNameAndValue('label1', 'label_value')
	),
	Gauge::fromValueAndTimestamp(0.12, time())->withLabels(
		Label::fromNameAndValue('label2', 'label_value')
	)
)->withHelp('A helpful description of your measurement.');

$gauges->add(
	Gauge::fromValue(3.45),
	Gauge::fromValueAndTimestamp(67.8, time()),
	Gauge::fromValue(90.1)->withLabels(
		Label::fromLabelString('label3="label_value"')
	)
);

$labels = LabelCollection::fromAssocArray(
	[
        'label4' => 'label_value',		
        'label5' => 'label_value'		
    ]
);

$gauges->add(
	Gauge::fromValueAndTimestamp(23.4, time())->withLabelCollection($labels)
);

var_dump($gauges->getMetricLines());
```

#### Prints

```
# TYPE your_metric_name gauge
# HELP your_metric_name A helpful description of your measurement.
your_metric_name 12.300000
your_metric_name -45.600000 1541323799
your_metric_name{label1="label_value"} 78.900000
your_metric_name{label2="label_value"} 0.120000 1541323799
your_metric_name 3.450000
your_metric_name 67.800000 1541323799
your_metric_name{label3="label_value"} 90.100000
your_metric_name{label4="label_value", label5="label_value"} 23.400000 1541323799
```

### Create a histogram out of a gauge collection and respond it

See [examples/histogram.php](./examples/histogram.php).

```php
<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetrics\Exposition\Text\Collections\GaugeCollection;
use OpenMetrics\Exposition\Text\Metrics\Gauge;
use OpenMetrics\Exposition\Text\Metrics\Histogram;
use OpenMetrics\Exposition\Text\Types\MetricName;

$values = [12.3, 45.6, 78.9, 0.12, 34.5];

$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'your_metric_name' ) );

foreach ( $values as $value )
{
	$gauges->add( Gauge::fromValue( $value ) );
}

# Create the histogram out of the gauge collection and suffix the metric name with "_histogram"
$histogram = Histogram::fromGaugeCollectionWithBounds( $gauges, [0.13, 30, 46, 78.9, 90], '_histogram' )
                      ->withHelp( 'Explanation of the histogram' );

var_dump($historgram->getMetricLines());
```

#### Prints

```
# TYPE your_metric_name_histogram histogram
# HELP your_metric_name_histogram Explanation of the histogram
your_metric_name_histogram_bucket{le="0.13"} 1
your_metric_name_histogram_bucket{le="30.9"} 2
your_metric_name_histogram_bucket{le="46.0"} 4
your_metric_name_histogram_bucket{le="78.9"} 5
your_metric_name_histogram_bucket{le="90.0"} 5
your_metric_name_histogram_bucket{le="+Inf"} 5
your_metric_name_histogram_sum 171.420000
your_metric_name_histogram_count 5
```

### Create a summary out of a gauge collection and respond it

See [examples/summary.php](./examples/summary.php).

```php
<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetrics\Exposition\Text\Collections\GaugeCollection;
use OpenMetrics\Exposition\Text\Metrics\Gauge;
use OpenMetrics\Exposition\Text\Metrics\Summary;
use OpenMetrics\Exposition\Text\Types\MetricName;

$values = [1.0, 1.2, 2.0, 2.5, 2.9, 3.1, 4.0, 4.4, 5.0, 9.9];

$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'your_metric_name' ) );

foreach ( $values as $value )
{
	$gauges->add( Gauge::fromValue( $value ) );
}

# Create the summary out of the gauge collection and suffix the metric name with "_summary"
$summary = Summary::fromGaugeCollectionWithQuantiles( $gauges, [0.3, 0.5, 0.75, 0.9], '_summary' )
                  ->withHelp( 'Explanation of the summary' );

var_dump($summary->getMetricLines());
```

#### Prints

```
# TYPE your_metric_name_summary summary
# HELP your_metric_name_summary Explanation of the summary
your_metric_name_summary{quantile="0.3"} 2.000000
your_metric_name_summary{quantile="0.5"} 2.900000
your_metric_name_summary{quantile="0.75"} 4.400000
your_metric_name_summary{quantile="0.9"} 5.000000
your_metric_name_summary_sum 36.000000
your_metric_name_summary_count 10
```

## Contributing

Contributions are welcome and will be fully credited. 
Please see the [contribution guide](.github/CONTRIBUTING.md) for details.


