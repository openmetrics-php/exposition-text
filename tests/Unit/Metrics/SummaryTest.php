<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;

final class SummaryTest extends TestCase
{
	/**
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetMetricsStringFromGaugeCollectionWithHelpText() : void
	{
		$gaugeCollection = GaugeCollection::fromGauges(
			MetricName::fromString( 'unit_test_metric' ),
			Gauge::fromValue( 0.1 ),
			Gauge::fromValue( 0.2 ),
			Gauge::fromValue( 0.3 ),
			Gauge::fromValue( 0.4 ),
			Gauge::fromValue( 0.5 ),
			Gauge::fromValue( 0.55 ),
			Gauge::fromValue( 0.6 ),
			Gauge::fromValue( 0.7 ),
			Gauge::fromValue( 0.8 ),
			Gauge::fromValue( 0.83 ),
			Gauge::fromValue( 0.9 ),
			Gauge::fromValue( 1.0 )
		);

		$summary = Summary::fromGaugeCollectionWithQuantiles(
			$gaugeCollection,
			[0.5, 0.9],
			'_summary'
		)->withHelp( 'Summary of gauges' );

		$expectedMetricsString = "# TYPE unit_test_metric_summary summary\n";
		$expectedMetricsString .= "# HELP unit_test_metric_summary Summary of gauges\n";
		$expectedMetricsString .= "unit_test_metric_summary{quantile=\"0.5\"} 0.550000\n";
		$expectedMetricsString .= "unit_test_metric_summary{quantile=\"0.9\"} 0.900000\n";
		$expectedMetricsString .= "unit_test_metric_summary_sum 6.880000\n";
		$expectedMetricsString .= 'unit_test_metric_summary_count 12';

		$this->assertSame( $expectedMetricsString, $summary->getMetricsString() );
	}

	/**
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetMetricsStringFromGaugeCollectionWithoutHelpText() : void
	{
		$gaugeCollection = GaugeCollection::fromGauges(
			MetricName::fromString( 'unit_test_metric' ),
			Gauge::fromValue( 0.1 ),
			Gauge::fromValue( 0.2 ),
			Gauge::fromValue( 0.3 ),
			Gauge::fromValue( 0.4 ),
			Gauge::fromValue( 0.5 ),
			Gauge::fromValue( 0.55 ),
			Gauge::fromValue( 0.6 ),
			Gauge::fromValue( 0.7 ),
			Gauge::fromValue( 0.8 ),
			Gauge::fromValue( 0.83 ),
			Gauge::fromValue( 0.9 ),
			Gauge::fromValue( 1.0 )
		);

		$summary = Summary::fromGaugeCollectionWithQuantiles(
			$gaugeCollection,
			[0.5, 0.9],
			'_summary'
		);

		$expectedMetricsString = "# TYPE unit_test_metric_summary summary\n";
		$expectedMetricsString .= "unit_test_metric_summary{quantile=\"0.5\"} 0.550000\n";
		$expectedMetricsString .= "unit_test_metric_summary{quantile=\"0.9\"} 0.900000\n";
		$expectedMetricsString .= "unit_test_metric_summary_sum 6.880000\n";
		$expectedMetricsString .= 'unit_test_metric_summary_count 12';

		$this->assertSame( $expectedMetricsString, $summary->getMetricsString() );
	}
}
