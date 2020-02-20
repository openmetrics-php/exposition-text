<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Collections;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
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
		$collection = GaugeCollection::withMetricName( $metricName );

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCount() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::withMetricName( $metricName );

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );

		$collection->add( Gauge::fromValue( 12.3 ) );
		$collection->add( Gauge::fromValue( 45.6 ) );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNewInstanceFromGauges() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$gauges     = [
			Gauge::fromValue( 12.3 ),
			Gauge::fromValue( 45.6 ),
		];

		$collection = GaugeCollection::fromGauges( $metricName, ...$gauges );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddGauges() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$gauges     = [
			Gauge::fromValue( 12.3 ),
			Gauge::fromValue( 45.6 ),
		];

		$collection = GaugeCollection::withMetricName( $metricName );
		$collection->add( ...$gauges );
		$collection->add( Gauge::fromValueAndTimestamp( 78.9, time() ) );

		$this->assertCount( 3, $collection );
		$this->assertSame( 3, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
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
		$expectedMetricStrings .= "unit_test_metric{unit=\"test\"} 12.300000\n";
		$expectedMetricStrings .= 'unit_test_metric 45.600000';

		$gaugeWithTimestamp = Gauge::fromValueAndTimestamp( 78.9, $timestamp );

		$collection = GaugeCollection::fromGauges(
			$metricName,
			$gaugeWithTimestamp,
			Gauge::fromValue( 12.3 )->withLabels( Label::fromNameAndValue( 'unit', 'test' ) ),
			Gauge::fromValue( 45.6 )
		)->withHelp(
			'This is a test metric with timestamp'
		);

		$this->assertSame( $expectedMetricStrings, $collection->getMetricsString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testMetricsStringIsEmptyIfNoGaugesWereAdded() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::withMetricName( $metricName );

		$this->assertSame( '', $collection->getMetricsString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testHelpStringIsOmittedIfNotSet() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = GaugeCollection::fromGauges( $metricName, Gauge::fromValue( 12.3 ) );

		$expectedMetricString = "# TYPE unit_test_metric gauge\n";
		$expectedMetricString .= 'unit_test_metric 12.300000';

		$this->assertSame( $expectedMetricString, $collection->getMetricsString() );
	}
}
