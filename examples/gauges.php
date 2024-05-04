<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

require __DIR__ . '/../vendor/autoload.php';

$gauges = GaugeCollection::fromGauges(
	MetricName::fromString( 'your_metric_name' ),
	Gauge::fromValue( 12.3 ),
	Gauge::fromValueAndTimestamp( -45.6, time() ),
	Gauge::fromValue( 78.9 )->withLabels(
		Label::fromNameAndValue( 'label1', 'label_value' )
	),
	Gauge::fromValueAndTimestamp( 0.12, time() )->withLabels(
		Label::fromNameAndValue( 'label2', 'label_value' )
	)
)->withHelp( 'A helpful description of your measurement.' );

$gauges->add(
	Gauge::fromValue( 3.45 ),
	Gauge::fromValueAndTimestamp( 67.8, time() ),
	Gauge::fromValue( 90.1 )->withLabels(
		Label::fromLabelString( 'label3="label_value"' )
	)
);

$labels = LabelCollection::fromAssocArray(
	[
		'label4' => 'label_value',
		'label5' => 'label_value',
	]
);

$gauges->add(
	Gauge::fromValueAndTimestamp( 23.4, time() )->withLabelCollection( $labels )
);

echo $gauges->getMetricsString();