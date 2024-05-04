<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

require __DIR__ . '/../vendor/autoload.php';

$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'your_metric_name' ) );

$loops = $argv[1];

for ( $i = 1; $i < $loops + 1; $i++ )
{
	$gauges->add( Gauge::fromValue( $i / 100 ) );
}

echo "\n";

# Create the summary out of the gauge collection and suffix the metric name with "_summary"
$summary = Summary::fromGaugeCollectionWithQuantiles( $gauges, [0.25, 0.5, 0.75, 0.9], '_summary' )
                  ->withHelp( 'Explanation of the summary' );

echo $summary->getMetricsString();