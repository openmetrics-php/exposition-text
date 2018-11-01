<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
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

		$this->assertSame( '', $metrics->getMetricStrings() );
	}

	/**
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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

		$this->assertSame( $expectedMetricStrings, $metrics->getMetricStrings() );
	}

	/**
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetMetricStringsForMultipleGauges() : void
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

		$this->assertSame( $expectedMetricStrings, $metrics->getMetricStrings() );
	}
}
