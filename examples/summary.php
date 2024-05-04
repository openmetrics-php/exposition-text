<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

require __DIR__ . '/../vendor/autoload.php';

$values = [1.0, 1.2, 2.0, 2.5, 2.9, 3.1, 4.0, 4.4, 5.0, 9.9];

$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'your_metric_name' ) );

foreach ( $values as $value )
{
	$gauges->add( Gauge::fromValue( $value ) );
}

# Create the summary out of the gauge collection and suffix the metric name with "_summary"
$summary = Summary::fromGaugeCollectionWithQuantiles( $gauges, [0.3, 0.5, 0.75, 0.9], '_summary' )
                  ->withHelp( 'Explanation of the summary' );

echo $summary->getMetricsString();