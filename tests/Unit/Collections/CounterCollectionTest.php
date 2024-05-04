<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Collections;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;

final class CounterCollectionTest extends TestCase
{
	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNewInstance() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = CounterCollection::withMetricName( $metricName );

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
		$collection = CounterCollection::withMetricName( $metricName );

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );

		$collection->add( Counter::fromValue( 12.3 ) );
		$collection->add( Counter::fromValue( 45.6 ) );

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
		$counters   = [
			Counter::fromValue( 12.3 ),
			Counter::fromValue( 45.6 ),
		];

		$collection = CounterCollection::fromCounters( $metricName, ...$counters );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddCounters() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$counters   = [
			Counter::fromValue( 12.3 ),
			Counter::fromValue( 45.6 ),
		];

		$collection = CounterCollection::withMetricName( $metricName );
		$collection->add( ...$counters );
		$collection->add( Counter::fromValueAndTimestamp( 78.9, time() ) );

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
		$expectedMetricStrings = "# TYPE unit_test_metric counter\n";
		$expectedMetricStrings .= "# HELP unit_test_metric This is a test metric with timestamp\n";
		$expectedMetricStrings .= "unit_test_metric_total 78.900000 {$timestamp}\n";
		$expectedMetricStrings .= "unit_test_metric_total{unit=\"test\"} 12.300000\n";
		$expectedMetricStrings .= 'unit_test_metric_total 45.600000';

		$counterWithTimestamp = Counter::fromValueAndTimestamp( 78.9, $timestamp );

		$collection = CounterCollection::fromCounters(
			$metricName,
			$counterWithTimestamp,
			Counter::fromValue( 12.3 )->withLabels( Label::fromNameAndValue( 'unit', 'test' ) ),
			Counter::fromValue( 45.6 )
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
	public function testMetricsStringIsEmptyIfNoCountersWereAdded() : void
	{
		$metricName = MetricName::fromString( 'unit_test_metric' );
		$collection = CounterCollection::withMetricName( $metricName );

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
		$collection = CounterCollection::fromCounters( $metricName, Counter::fromValue( 12.3 ) );

		$expectedMetricString = "# TYPE unit_test_metric counter\n";
		$expectedMetricString .= 'unit_test_metric_total 12.300000';

		$this->assertSame( $expectedMetricString, $collection->getMetricsString() );
	}
}
