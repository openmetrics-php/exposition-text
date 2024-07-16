<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Integration\OpenMetrics;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesMetricLines;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function file_put_contents;
use function shell_exec;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class PythonParserTest extends TestCase
{
	/**
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanParseCounterMetricsWithPythonParser() : void
	{
		$expectedParserOutput = "Name: a_total Labels: {'foo': '4'} Value: 1.0 Timestamp: 1234567.000000000\n"
		                        . "Name: a_total Labels: {'bar': 'foo'} Value: 2.0 Timestamp: 1234567.000000000\n"
		                        . "Name: a_total Labels: {} Value: 3.0 Timestamp: None\n";

		$collection = CounterCollection::fromCounters(
			MetricName::fromString( 'a' ),
			Counter::fromValueAndTimestamp( 1, 1234567 )
			       ->withLabels(
				       Label::fromNameAndValue( 'foo', '4' )
			       ),
			Counter::fromValueAndTimestamp( 2, 1234567 )
			       ->withLabels(
				       Label::fromNameAndValue( 'bar', 'foo' )
			       ),
			Counter::fromValue( 3 )
		);

		$this->assertParsedMetricOutput( $expectedParserOutput, $collection );
	}

	/**
	 * @param string              $expectedParserOutput
	 * @param ProvidesMetricLines $metrics
	 *
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	private function assertParsedMetricOutput( string $expectedParserOutput, ProvidesMetricLines $metrics ) : void
	{
		$filename = tempnam( sys_get_temp_dir(), 'PythonParserTest_' );
		$command = sprintf( 'cat "%s" | python %s/parseFile.py', $filename, __DIR__ );

		file_put_contents( $filename, $metrics->getMetricsString() . "\n# EOF" );

		$output = shell_exec( $command );

		$this->assertSame( $expectedParserOutput, $output );

		@unlink( $filename );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanParseGaugeMetricsWithPythonParser() : void
	{
		$expectedParserOutput = "Name: gauge Labels: {'foo': 'bar'} Value: 1.01 Timestamp: 1234567.000000000\n"
		                        . "Name: gauge Labels: {'foobar': 'foo', 'bar': 'foo'} Value: 2.0202 Timestamp: 1234567.000000000\n"
		                        . "Name: gauge Labels: {} Value: 3.0302 Timestamp: None\n";

		$collection = GaugeCollection::fromGauges(
			MetricName::fromString( 'gauge' ),
			Gauge::fromValueAndTimestamp( 1.01, 1234567 )->withLabels(
				Label::fromNameAndValue( 'foo', 'bar' )
			),
			Gauge::fromValueAndTimestamp( 2.0202, 1234567 )
            ->withLabels(Label::fromNameAndValue( 'foobar', 'foo' ))
            ->withLabels(Label::fromNameAndValue( 'bar', 'foo' ) ),
			Gauge::fromValue( 3.0302 )
		);

		$this->assertParsedMetricOutput( $expectedParserOutput, $collection );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanParseSummaryMetricsWithPythonParser() : void
	{
		$expectedParserOutput = "Name: gauge_summary Labels: {'quantile': '0.3'} Value: 2.0 Timestamp: None\n"
		                        . "Name: gauge_summary Labels: {'quantile': '0.5'} Value: 2.9 Timestamp: None\n"
		                        . "Name: gauge_summary Labels: {'quantile': '0.75'} Value: 4.4 Timestamp: None\n"
		                        . "Name: gauge_summary Labels: {'quantile': '0.9'} Value: 5.0 Timestamp: None\n"
		                        . "Name: gauge_summary_sum Labels: {} Value: 36.0 Timestamp: None\n"
		                        . "Name: gauge_summary_count Labels: {} Value: 10 Timestamp: None\n";

		$values = [1.0, 1.2, 2.0, 2.5, 2.9, 3.1, 4.0, 4.4, 5.0, 9.9];
		$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'gauge' ) );
		foreach ( $values as $value )
		{
			$gauges->add( Gauge::fromValue( $value ) );
		}
		$summary = Summary::fromGaugeCollectionWithQuantiles( $gauges, [0.3, 0.5, 0.75, 0.9], '_summary' );

		$this->assertParsedMetricOutput( $expectedParserOutput, $summary );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanParseHistogramMetricsWithPythonParser() : void
	{
		$expectedParserOutput = "Name: gauge_histogram_bucket Labels: {'le': '0.13'} Value: 1 Timestamp: None\n"
		                        . "Name: gauge_histogram_bucket Labels: {'le': '31.0'} Value: 2 Timestamp: None\n"
		                        . "Name: gauge_histogram_bucket Labels: {'le': '46.0'} Value: 4 Timestamp: None\n"
		                        . "Name: gauge_histogram_bucket Labels: {'le': '78.9'} Value: 5 Timestamp: None\n"
		                        . "Name: gauge_histogram_bucket Labels: {'le': '90.0'} Value: 5 Timestamp: None\n"
		                        . "Name: gauge_histogram_bucket Labels: {'le': '+Inf'} Value: 5 Timestamp: None\n"
		                        . "Name: gauge_histogram_sum Labels: {} Value: 171.42 Timestamp: None\n"
		                        . "Name: gauge_histogram_count Labels: {} Value: 5 Timestamp: None\n";

		$values = [12.3, 45.6, 78.9, 0.12, 34.5];
		$gauges = GaugeCollection::withMetricName( MetricName::fromString( 'gauge' ) );
		foreach ( $values as $value )
		{
			$gauges->add( Gauge::fromValue( $value ) );
		}
		$histogram = Histogram::fromGaugeCollectionWithBounds( $gauges, [0.13, 31, 46, 78.9, 90], '_histogram' );

		$this->assertParsedMetricOutput( $expectedParserOutput, $histogram );
	}
}
