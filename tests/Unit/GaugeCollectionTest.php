<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Exceptions\MetricNameMismatchException;
use OpenMetricsPhp\Exposition\Text\Gauge;
use OpenMetricsPhp\Exposition\Text\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\MetricName;
use PHPUnit\Framework\TestCase;

final class GaugeCollectionTest extends TestCase
{

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNewInstance() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::new( $metricName );

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws MetricNameMismatchException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCount() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::new( $metricName );

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );

		$collection->add( Gauge::fromMetricNameAndValue( $metricName, 12.3 ) );
		$collection->add( Gauge::fromMetricNameAndValue( $metricName, 45.6 ) );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws MetricNameMismatchException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNewInstanceFromGauges() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$gauges     = [
			Gauge::fromMetricNameAndValue( $metricName, 12.3 ),
			Gauge::fromMetricNameAndValue( $metricName, 45.6 ),
		];

		$collection = GaugeCollection::fromGauges( $metricName, ...$gauges );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws MetricNameMismatchException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddGauges() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$gauges     = [
			Gauge::fromMetricNameAndValue( $metricName, 12.3 ),
			Gauge::fromMetricNameAndValue( $metricName, 45.6 ),
		];

		$collection = GaugeCollection::new( $metricName );
		$collection->add( ...$gauges );
		$collection->add( Gauge::fromMetricNameValueAndTimestamp( $metricName, 78.9, time() ) );

		$this->assertCount( 3, $collection );
		$this->assertSame( 3, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws MetricNameMismatchException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetMetricStrings() : void
	{
		$timestamp             = time();
		$metricName            = MetricName::fromString( 'unit_test_metric' );
		$expectedMetricStrings = "# TYPE unit_test_metric gauge\n";
		$expectedMetricStrings .= "# HELP unit_test_metric This is a test metric with timestamp\n";
		$expectedMetricStrings .= "unit_test_metric 78.900000 {$timestamp}\n";
		$expectedMetricStrings .= "unit_test_metric 12.300000\n";
		$expectedMetricStrings .= "# HELP unit_test_metric This is a second sample with help\n";
		$expectedMetricStrings .= 'unit_test_metric 45.600000';

		$gaugeWithHelp = Gauge::fromMetricNameValueAndTimestamp( $metricName, 78.9, $timestamp );
		$gaugeWithHelp->setHelp( 'This is a test metric with timestamp' );

		$lastGaugeWithHelp = Gauge::fromMetricNameAndValue( $metricName, 45.6 );
		$lastGaugeWithHelp->setHelp( 'This is a second sample with help' );

		$gauges = [
			Gauge::fromMetricNameAndValue( $metricName, 12.3 ),
			$lastGaugeWithHelp,
		];

		$collection = GaugeCollection::new( $metricName );

		$this->assertSame( '', $collection->getMetricStrings() );

		$collection->add( $gaugeWithHelp, ...$gauges );

		$this->assertSame( $expectedMetricStrings, $collection->getMetricStrings() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws MetricNameMismatchException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 */
	public function testThrowsExceptionWhenAddingGaugesWithDifferentMetricName() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::new( $metricName );
		$gauge      = Gauge::fromMetricNameAndValue(
			MetricName::fromString( 'test_unit_metric' ),
			12.3
		);

		$this->expectException( MetricNameMismatchException::class );
		$this->expectExceptionMessage(
			"Metric name in collection 'unit_test_metric' doesn't match metric name from item 'test_unit_metric'."
		);

		$collection->add( $gauge );

		$this->fail( 'Expected MetricNameMismatchException due to different metric names in collection and gauge.' );
	}
}
