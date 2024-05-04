<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram;
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
$summary = Histogram::fromGaugeCollectionWithBounds( $gauges, [10, 100, 500, 900], '_histogram' )
                    ->withHelp( 'Explanation of the histogram' );

echo $summary->getMetricsString();