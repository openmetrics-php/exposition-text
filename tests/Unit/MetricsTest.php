<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Exceptions\LogicException;
use OpenMetricsPhp\Exposition\Text\Metrics;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;

final class MetricsTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetNewInstance() : void
	{
		$metrics = Metrics::new();

		$this->assertSame( '', $metrics->getMetricsString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws LogicException
	 */
	public function testCollectGauges() : void
	{
		$timestamp = time();
		$metrics   = Metrics::new();
		$metrics->collectGauges( MetricName::fromString( 'unit_test_metric' ) )
		        ->withHelp( 'This helps understand the metric.' )
		        ->add(
			        Gauge::fromValue( 12.3 ),
			        Gauge::fromValue( 45.6 )
			             ->withLabels(
				             Label::fromNameAndValue( 'simple', 'label' )
			             ),
			        Gauge::fromValueAndTimestamp( 78.9, $timestamp )
			             ->withLabelCollection(
				             LabelCollection::fromAssocArray(
					             [
						             'unit' => 'test',
						             'test' => 'unit',
					             ]
				             )
			             )
		        );

		$expectedMetricStrings = "# TYPE unit_test_metric gauge\n";
		$expectedMetricStrings .= "# HELP unit_test_metric This helps understand the metric.\n";
		$expectedMetricStrings .= "unit_test_metric 12.300000\n";
		$expectedMetricStrings .= "unit_test_metric{simple=\"label\"} 45.600000\n";
		$expectedMetricStrings .= "unit_test_metric{unit=\"test\", test=\"unit\"} 78.900000 {$timestamp}";

		$this->assertSame( $expectedMetricStrings, $metrics->getMetricsString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws LogicException
	 */
	public function testCanGetMetricStringsForMultipleGauges() : void
	{
		$timestamp             = time();
		$metrics               = Metrics::new();
		$metrics->collectGauges( MetricName::fromString( 'unit_test_metric' ) )
		        ->withHelp( 'This helps understand the metric.' )
		        ->add(
			        Gauge::fromValue( 12.3 ),
			        Gauge::fromValue( 45.6 )
			             ->withLabels(
				             Label::fromNameAndValue( 'simple', 'label' )
			             ),
			        Gauge::fromValueAndTimestamp( 78.9, $timestamp )
			             ->withLabelCollection(
				             LabelCollection::fromAssocArray(
					             [
						             'unit' => 'test',
						             'test' => 'unit',
					             ]
				             )
			             )
		        );

		$metrics->collectGauges( MetricName::fromString( 'test_unit_metric' ) )
			->withHelp( 'Second help text.' )
			->add(
				Gauge::fromValue( 12.3 ),
				Gauge::fromValue( 45.6 )
					->withLabels(
						Label::fromNameAndValue( 'simple', 'label' )
					),
				Gauge::fromValueAndTimestamp( 78.9, $timestamp )
					->withLabelCollection(
						LabelCollection::fromAssocArray(
							[
								'unit' => 'test',
								'test' => 'unit',
							]
						)
					)
			);

		$expectedMetricStrings = "# TYPE unit_test_metric gauge\n";
		$expectedMetricStrings .= "# HELP unit_test_metric This helps understand the metric.\n";
		$expectedMetricStrings .= "unit_test_metric 12.300000\n";
		$expectedMetricStrings .= "unit_test_metric{simple=\"label\"} 45.600000\n";
		$expectedMetricStrings .= "unit_test_metric{unit=\"test\", test=\"unit\"} 78.900000 {$timestamp}";

		$expectedMetricStrings .= "\n";

		$expectedMetricStrings .= "# TYPE test_unit_metric gauge\n";
		$expectedMetricStrings .= "# HELP test_unit_metric Second help text.\n";
		$expectedMetricStrings .= "test_unit_metric 12.300000\n";
		$expectedMetricStrings .= "test_unit_metric{simple=\"label\"} 45.600000\n";
		$expectedMetricStrings .= "test_unit_metric{unit=\"test\", test=\"unit\"} 78.900000 {$timestamp}";

		$this->assertSame( $expectedMetricStrings, $metrics->getMetricsString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws LogicException
	 */
	public function testCanCountAllCollectedMetrics() : void
	{
		$metrics = Metrics::new();
		$metrics->collectGauges( MetricName::fromString( 'unit_test_gauges' ) )
		        ->withHelp( 'Gauges' )
		        ->add(
			        Gauge::fromValue( 12.3 ),
			        Gauge::fromValue( 45.6 ),
			        Gauge::fromValue( 78.9 )
		        );
		$metrics->collectCounters( MetricName::fromString( 'unit_test_counters' ) )
		        ->withHelp( 'Counters' )
		        ->add(
			        Metrics\Counter::fromValue( 12 ),
			        Metrics\Counter::fromValue( 34 ),
			        Metrics\Counter::fromValue( 56 )
		        );

		$this->assertCount( 6, $metrics );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws LogicException
	 */
	public function testThrowsMismatchExceptionWhenCollectingDifferentMetricsWithSameName() : void
	{
		$metrics = Metrics::new();
		$metrics->collectGauges( MetricName::fromString( 'unit_test_metric' ) )
		        ->withHelp( 'Gauges' )
		        ->add( Gauge::fromValue( 12.3 ) );

		$this->expectException( LogicException::class );
		$this->expectExceptionMessage(
			sprintf(
				'Requested collection type does not match existing collection: Requested %s vs. existing %s',
				CounterCollection::class,
				GaugeCollection::class
			)
		);

		$metrics->collectCounters( MetricName::fromString( 'unit_test_metric' ) )
		        ->withHelp( 'Counters' )
		        ->add( Metrics\Counter::fromValue( 12 ) );
	}
}
