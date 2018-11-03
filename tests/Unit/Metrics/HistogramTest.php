<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;

final class HistogramTest extends TestCase
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

		$histogram = Histogram::fromGaugeCollectionWithBounds(
			$gaugeCollection,
			[0.3, 0.6, 0.9],
			'_histogram'
		)->withHelp( 'Histogram of gauges' );

		$expectedMetricsString = "# TYPE unit_test_metric_histogram histogram\n";
		$expectedMetricsString .= "# HELP unit_test_metric_histogram Histogram of gauges\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.3\"} 3\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.6\"} 7\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.9\"} 11\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"+Inf\"} 12\n";
		$expectedMetricsString .= "unit_test_metric_histogram_sum 6.880000\n";
		$expectedMetricsString .= 'unit_test_metric_histogram_count 12';

		$this->assertSame( $expectedMetricsString, $histogram->getMetricsString() );
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

		$histogram = Histogram::fromGaugeCollectionWithBounds(
			$gaugeCollection,
			[0.3, 0.6, 0.9],
			'_histogram'
		);

		$expectedMetricsString = "# TYPE unit_test_metric_histogram histogram\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.3\"} 3\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.6\"} 7\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"0.9\"} 11\n";
		$expectedMetricsString .= "unit_test_metric_histogram_bucket{le=\"+Inf\"} 12\n";
		$expectedMetricsString .= "unit_test_metric_histogram_sum 6.880000\n";
		$expectedMetricsString .= 'unit_test_metric_histogram_count 12';

		$this->assertSame( $expectedMetricsString, $histogram->getMetricsString() );
	}
}
