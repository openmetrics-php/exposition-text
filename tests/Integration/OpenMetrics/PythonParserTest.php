<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Integration\OpenMetrics;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;
use function dirname;
use function file_put_contents;
use function shell_exec;
use function unlink;

final class PythonParserTest extends TestCase
{
	/**
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanParseCounterMetricsWithPythonParser() : void
	{
		$filename       = dirname( __DIR__, 3 ) . '/build/counter_metrics.txt';
		$command        = sprintf( 'python %s/parseFile.py %s', __DIR__, $filename );
		$expectedOutput = "Name: a_total Labels: {u'foo': u'4'} Value: 1.0 Timestamp: 1234567.000000000\n";

		$collection = CounterCollection::fromCounters(
			MetricName::fromString( 'a' ),
			Counter::fromValueAndTimestamp( 1, 1234567 )
			       ->withLabels(
				       Label::fromNameAndValue( 'foo', '4' )
			       )
		);

		file_put_contents( $filename, $collection->getMetricsString() . "\n# EOF" );

		$output = shell_exec( $command );

		$this->assertSame( $expectedOutput, $output );

		@unlink( $filename );
	}
}
