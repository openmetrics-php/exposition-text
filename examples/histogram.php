<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

require __DIR__ . '/../vendor/autoload.php';

$values = [12.3, 45.6, 78.9, 0.12, 34.5];

$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'your_metric_name' ) );

foreach ( $values as $value )
{
	$gauges->add( Gauge::fromValue( $value ) );
}

# Create the histogram out of the gauge collection and suffix the metric name with "_histogram"
$histogram = Histogram::fromGaugeCollectionWithBounds( $gauges, [30, 46, 78.9, 90], '_histogram' )
                      ->withHelp( 'Explanation of the histogram' );

echo $histogram->getMetricsString();