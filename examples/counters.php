<?php declare(strict_types=1);

namespace YourVendor\YourProject;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

require __DIR__ . '/../vendor/autoload.php';

$counters = CounterCollection::fromCounters(
	MetricName::fromString( 'your_metric_name' ),
	Counter::fromValue( 1 ),
	Counter::fromValueAndTimestamp( 2, time() ),
	Counter::fromValue( 3 )->withLabels(
		Label::fromNameAndValue( 'label1', 'label_value' )
	),
	Counter::fromValueAndTimestamp( 4, time() )->withLabels(
		Label::fromNameAndValue( 'label2', 'label_value' )
	)
)->withHelp( 'A helpful description of your measurement.' );

# Add counters after creating the collection
$counters->add(
	Counter::fromValue( 5 ),
	Counter::fromValueAndTimestamp( 6, time() ),
	Counter::fromValue( 7 )->withLabels(
	# Create labels from label string
		Label::fromLabelString( 'label3="label_value"' )
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
	Counter::fromValueAndTimestamp( 8, time() )->withLabelCollection( $labels )
);

echo $counters->getMetricsString();